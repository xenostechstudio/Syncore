<?php

namespace App\Models\Invoicing;

use App\Enums\InvoiceState;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Traits\HasAttachments;
use App\Traits\HasNotes;
use App\Traits\HasSoftDeletes;
use App\Traits\HasStateMachine;
use App\Traits\HasYearlySequenceNumber;
use App\Traits\LogsActivity;
use App\Traits\Searchable;
use Database\Factories\Invoicing\InvoiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory, LogsActivity, HasNotes, HasSoftDeletes, HasYearlySequenceNumber, Searchable, HasAttachments, HasStateMachine;

    protected string $stateEnum = InvoiceState::class;

    public const NUMBER_PREFIX = 'INV';
    public const NUMBER_COLUMN = 'invoice_number';
    public const NUMBER_DIGITS = 5;

    protected array $logActions = ['created', 'updated', 'deleted'];
    
    protected array $searchable = ['invoice_number', 'notes', 'terms'];

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'sales_order_id',
        'user_id',
        'invoice_date',
        'due_date',
        'status',
        'xendit_invoice_id',
        'xendit_invoice_url',
        'xendit_external_id',
        'xendit_status',
        'share_token',
        'share_token_expires_at',
        'subtotal',
        'tax',
        'discount',
        'total',
        'notes',
        'terms',
        'paid_amount',
        'paid_date',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'share_token_expires_at' => 'datetime',
    ];

    public function send(): bool
    {
        if (!$this->state->canSend()) {
            return false;
        }
        return $this->transitionTo(InvoiceState::SENT);
    }

    public function markAsPaid(): bool
    {
        if ($this->state->isTerminal()) {
            return false;
        }
        return $this->transitionTo(InvoiceState::PAID);
    }

    public function markAsPartial(): bool
    {
        if (!$this->state->canRegisterPayment()) {
            return false;
        }
        return $this->transitionTo(InvoiceState::PARTIAL);
    }

    public function markAsOverdue(): bool
    {
        if ($this->state->isTerminal()) {
            return false;
        }
        return $this->transitionTo(InvoiceState::OVERDUE);
    }

    public function cancelInvoice(): bool
    {
        if (!$this->state->canCancel()) {
            return false;
        }
        return $this->transitionTo(InvoiceState::CANCELLED);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function ensureShareToken(bool $forceRefresh = false): self
    {
        if (
            $forceRefresh
            || blank($this->share_token)
            || ($this->share_token_expires_at && $this->share_token_expires_at->isPast())
        ) {
            $this->share_token = Str::random(48);
            $this->share_token_expires_at = now()->addDays(30);
            $this->save();
        }

        return $this;
    }
}
