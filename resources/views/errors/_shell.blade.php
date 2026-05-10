{{--
    Shared shell for branded error pages. Pulls only from config (env)
    so the page renders even when the DB is down — CompanyProfile would
    query, which is the wrong move on a 503 page where the DB is the
    likely cause of the error.

    Required vars: $status (int), $title (string), $message (string)
    Optional:      $cta (array of ['label' => ..., 'href' => ...])
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $status }} — {{ $title }} · {{ config('app.name') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />
        <style>
            *, *::before, *::after { box-sizing: border-box; }
            body {
                margin: 0;
                font-family: 'Inter', system-ui, -apple-system, sans-serif;
                background: #fafafa;
                color: #18181b;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 24px;
            }
            @media (prefers-color-scheme: dark) {
                body { background: #09090b; color: #fafafa; }
                .card { background: #18181b !important; border-color: #27272a !important; }
                .muted { color: #a1a1aa !important; }
                .btn { background: #fafafa !important; color: #18181b !important; }
                .btn:hover { background: #e4e4e7 !important; }
                .btn-secondary { background: transparent !important; color: #a1a1aa !important; border: 1px solid #3f3f46 !important; }
                .btn-secondary:hover { color: #fafafa !important; border-color: #52525b !important; }
            }
            .card {
                background: #fff;
                border: 1px solid #e4e4e7;
                border-radius: 12px;
                padding: 40px;
                max-width: 480px;
                width: 100%;
                text-align: center;
            }
            .status {
                font-size: 64px;
                font-weight: 600;
                letter-spacing: -0.04em;
                margin: 0;
                line-height: 1;
            }
            .title { font-size: 20px; font-weight: 600; margin: 16px 0 8px; }
            .muted { color: #71717a; font-size: 14px; line-height: 1.5; margin: 0; }
            .actions { margin-top: 28px; display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; }
            .btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 8px 16px;
                background: #18181b;
                color: #fff;
                border-radius: 8px;
                text-decoration: none;
                font-weight: 500;
                font-size: 14px;
                border: 1px solid transparent;
                transition: background-color 150ms ease;
            }
            .btn:hover { background: #27272a; }
            .btn-secondary {
                background: transparent;
                color: #52525b;
                border-color: #e4e4e7;
            }
            .btn-secondary:hover { color: #18181b; border-color: #a1a1aa; }
            .brand {
                margin-top: 32px;
                font-size: 12px;
                color: #a1a1aa;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div>
            <div class="card">
                <p class="status">{{ $status }}</p>
                <p class="title">{{ $title }}</p>
                <p class="muted">{{ $message }}</p>
                <div class="actions">
                    @foreach($cta ?? [] as $action)
                        <a href="{{ $action['href'] }}" class="btn {{ $action['secondary'] ?? false ? 'btn-secondary' : '' }}">
                            {{ $action['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
            <p class="brand">{{ config('app.name') }}</p>
        </div>
    </body>
</html>
