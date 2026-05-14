# Operator runbook

The day-to-day playbook for running Syncore in production. Covers
deploys, verification, monitoring, common operations, and incident
response. Setup (env vars, supervisor templates, scheduled tasks)
lives in [CONFIGURATION.md](CONFIGURATION.md) — this doc assumes
that's already done.

---

## Deploy cycle

After every release, run these in order. They take ~30 seconds end-to-
end and catch the most common deploy mistakes.

```bash
# 1. Pull the new code
git fetch origin
git reset --hard origin/main

# 2. Install + build
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# 3. Database
php artisan migrate --force

# 4. Cache config + routes + views (skip on dev; cached state pins prod)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 5. Restart queue worker (it has the OLD code cached in memory)
sudo supervisorctl restart syncore-queue:*

# 6. Verify
php artisan production:check
curl -fsS https://erp.example.com/api/health
```

If any of those fail, see the matching section below. **Don't ship a
release that fails `production:check`** unless you understand exactly
what the warning means.

---

## Post-deploy verification

### `php artisan production:check`

Sanity-checks the environment. Prints `OK`/`WARN`/`FAIL` per check with
a remediation tip. Flags include:

| Flag | What it does |
| --- | --- |
| (none) | Full check: env, key, URL, drivers, storage, Xendit, live probes |
| `--config-only` | Skip driver-specific checks + live backend probes. Use during CI / build steps where a Redis/DB isn't reachable. |
| `--strict` | Treat `WARN` as failures. Exit code 1 if anything is less than `OK`. Right for blocking CI pipelines. |

Exit codes: `0` = pass, `1` = at least one `FAIL` (or `WARN` with
`--strict`).

If the check finds `APP_DEBUG=true` on prod, stop and fix immediately —
stack traces will leak to customers and the fix is one env var change.

### `GET /api/health`

Returns 200 if database + cache reach + the queue probe is healthy.
Returns 503 with diagnostic JSON if any backend is unreachable.

```bash
$ curl -s https://erp.example.com/api/health | jq
{
  "database": { "status": "connected", "driver": "pgsql", "latency_ms": 4 },
  "cache":    { "status": "connected", "driver": "redis", "latency_ms": 1 },
  "queue":    { "status": "connected", "driver": "redis" }
}
```

Wire your monitoring (UptimeRobot / Pingdom / Datadog HTTP check)
against this endpoint — a 503 means something a customer will hit.

For richer diagnostics (table sizes, cache hit ratios), the
authenticated `GET /api/health/detailed` returns table-row counts +
database stats. Reserve for ad-hoc debugging.

---

## Day-to-day monitoring

### Queue worker

Must be running for: invoice/PO email notifications, Xendit webhook
post-processing, notification fan-out, attachment scans.

```bash
# Check the worker is alive
sudo supervisorctl status syncore-queue:*

# If it shows STOPPED or FATAL:
sudo supervisorctl restart syncore-queue:*

# Inspect the failed-jobs table — if non-empty, something's stuck:
php artisan queue:failed

# Retry a single failed job
php artisan queue:retry <uuid>

# Or retry everything failed in the last hour (use with care)
php artisan queue:retry all
```

A stalled queue usually surfaces as "notifications not arriving" or
"webhook payments not flipping invoice status." Confirm with
`queue:failed` before assuming the worker is broken.

### Scheduler

Crontab entry must be present:

```cron
* * * * * cd /var/www/syncore && php artisan schedule:run >> /dev/null 2>&1
```

Verify it's actually running:

```bash
# Should show recent invocations
sudo tail -f /var/log/syslog | grep CRON
```

If `schedule:run` is missing, the following will silently not happen:
- Daily overdue-invoice flip (sent → overdue past due_date)
- Daily payroll-period rollover
- Weekly database backup (if you configured it via the scheduler)

### Logs

```bash
# App logs (daily rotation)
ls -lht storage/logs/laravel-*.log | head

# Tail today's log for warnings + errors
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log

# Filter Xendit webhook activity
grep "XenditWebhook" storage/logs/laravel-$(date +%Y-%m-%d).log

# Errors only
grep -E "ERROR|CRITICAL" storage/logs/laravel-$(date +%Y-%m-%d).log
```

If Sentry is configured (`SENTRY_LARAVEL_DSN` set), check the Sentry
dashboard first — it groups errors by frequency and gives you the
stack trace + breadcrumbs without grepping.

---

## Common operations

### Bulk-import data

The canonical workflow when an operator needs to load or update many
records (customers, products, invoices, etc.):

1. From the index page, *Export* current data to get the column shape.
2. Edit the file in Excel — fill in the new rows, fix existing ones.
3. *Import* it back. The upsert key (typically `code`, `sku`, `email`,
   or the record's number) matches rows back to existing records.

Every importer collects and reports failures without aborting — see
[IMPORTS.md](IMPORTS.md) for required columns and behavior per
importer. Failed rows come back as a downloadable CSV that opens in
Excel, ready to fix and re-upload.

### Repair fulfillment drift

If the *Create Invoice* or *Create Delivery* button shows on a Sales
Order that already has invoices/deliveries, the `quantity_invoiced` /
`quantity_delivered` counters are out of sync with the underlying
data. Observer fan-out (commit `7421153`) prevents this in normal
flow, but raw DB inserts (migrations, manual SQL repair, third-party
sync) can still produce drift.

```bash
# Dry-run: show what would change without writing
php artisan sales-orders:reconcile-fulfillment --dry-run

# Apply
php artisan sales-orders:reconcile-fulfillment

# Repair a single order
php artisan sales-orders:reconcile-fulfillment --order=10
```

Safe to re-run — the command is idempotent.

### Regenerate a customer payment link

Xendit redirect URLs are signed with the route's signature middleware.
A change to `APP_KEY` or `APP_URL` invalidates every previously-issued
link. To regenerate one for a customer:

1. Open the invoice in admin.
2. Click *Share* → *Regenerate payment link*.
3. Send the new link to the customer.

If many links broke at once (e.g. after a key rotation), there's no
bulk-regen UI — links regenerate lazily when the customer clicks. Or
write a small one-off command if it's urgent.

### Rotate the Xendit webhook token

After rotating `XENDIT_WEBHOOK_TOKEN`:

1. Update the token in Xendit's dashboard (Settings → Webhooks).
2. Update `.env`, then `php artisan config:cache`.
3. Restart the queue worker (in-flight jobs cache config).
4. Trigger a test webhook from the Xendit dashboard — it should land
   200 (visible in the log: `XenditWebhook: PAID`).

If you skip step 3, in-flight jobs still hold the old token and will
reject the new webhook signature.

---

## Incident response

### Health endpoint returns 503

```bash
curl -s https://erp.example.com/api/health | jq
```

The response tells you which backend failed. Specifically:

- `database.status = error` → DB is unreachable. Check `pg_isready`,
  the connection pool, the credentials.
- `cache.status = error` → Redis (or whatever cache driver) is
  unreachable. `redis-cli ping`.
- `queue.status = error` → Queue table/connection is broken (database
  queue) or Redis queue prefix unreachable.

After fixing the backend, the health check returns 200 on the next
request — no app restart needed.

### Webhook payments not crediting invoices

1. Confirm Xendit is actually calling: check your Xendit dashboard
   webhook log for recent deliveries.
2. Inspect the most recent webhook in Syncore logs:
   ```bash
   grep "XenditWebhook" storage/logs/laravel-$(date +%Y-%m-%d).log | tail -20
   ```
3. If the log shows `401 invalid token`: token rotation issue — see
   *Rotate the Xendit webhook token* above.
4. If the log shows `503 production has no webhook token`: production
   is missing `XENDIT_WEBHOOK_TOKEN`. This refusal is intentional
   (commit `aaee382` fixed the open-default footgun). Set the env var,
   `config:cache`, restart workers.
5. If the log shows `200` but the invoice didn't flip: queue worker
   is stuck. The webhook controller returns 200 the moment it
   persists the payload; the actual invoice update happens in a
   queued job. Check `php artisan queue:failed`.

### A migration failed mid-deploy

Don't roll back without confirming what's in the DB. Schema mid-state
is the worst place to start from.

```bash
# What migrations have applied?
php artisan migrate:status

# If the failing migration is the LAST listed (status: Pending),
# it didn't apply — fix the migration file and re-run migrate.

# If it shows Ran but the schema is partially-built (e.g. table created
# but a column add failed), you need to either complete it manually or
# write a fix-up migration. NEVER edit a Ran migration without coordinating.
```

The driver-aware enum migration pattern in
[`CLAUDE.md`](../CLAUDE.md#driver-aware-status-enum-migrations) is the
most common source of partial-state failures — SQLite tests pass, but
Postgres rejects in production.

### Disk filling up

Logs rotate daily but `storage/logs/` can still grow if no retention
is set:

```bash
# Check log directory size
du -sh storage/logs/

# Activity log table can also bloat — keep last 90 days
php artisan activity-logs:cleanup

# Failed jobs table can accumulate
php artisan queue:prune-failed --hours=720
```

For application file uploads (`storage/app/`), check
`storage/app/attachments` — large customer-uploaded files. Don't
blindly delete; the matching `Attachment` rows will become broken
references. Use the admin *Attachments* page to remove records first.

### Backup verification

A backup you've never restored is a hope, not a backup. Quarterly:

```bash
# Dump production DB
pg_dump -Fc syncore_production > backup.dump

# Restore into a scratch DB
createdb syncore_restore_test
pg_restore -d syncore_restore_test backup.dump

# Sanity check: row counts roughly match
psql -d syncore_restore_test -c "
  SELECT 'invoices' as t, count(*) FROM invoices
  UNION ALL SELECT 'sales_orders', count(*) FROM sales_orders
  UNION ALL SELECT 'products', count(*) FROM products;
"

# Tear down
dropdb syncore_restore_test
```

Also confirm `storage/app/` is in your backup target. The DB without
attachments is half a backup.

---

## Reference: command index

Quick lookup for the commands referenced across this doc.

| Command | Purpose |
| --- | --- |
| `php artisan production:check` | Pre-deploy / post-deploy environment sanity |
| `php artisan production:check --config-only` | Same, skipping live driver probes |
| `php artisan production:check --strict` | Fail on warnings (use in CI) |
| `php artisan sales-orders:reconcile-fulfillment --dry-run` | Report fulfillment-counter drift |
| `php artisan sales-orders:reconcile-fulfillment` | Repair drift |
| `php artisan queue:failed` | List failed queued jobs |
| `php artisan queue:retry <uuid|all>` | Re-dispatch failed job(s) |
| `php artisan queue:prune-failed --hours=720` | Drop failed jobs older than 30 days |
| `php artisan migrate:status` | What's applied vs pending |
| `php artisan activity-logs:cleanup` | Trim activity log to last 90 days |
| `php artisan config:cache` | Lock in current `.env` for prod perf |
| `curl https://erp.example.com/api/health` | Liveness probe |
| `sudo supervisorctl restart syncore-queue:*` | Bounce queue worker after deploy or config change |

Per-importer column reference: [IMPORTS.md](IMPORTS.md).
Per-document state transitions: [STATE_MACHINES.md](STATE_MACHINES.md).
Cross-cutting architecture: [ARCHITECTURE.md](ARCHITECTURE.md).
