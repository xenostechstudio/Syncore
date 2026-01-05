<?php

namespace App\Livewire\HR\Payroll;

use App\Livewire\Concerns\WithNotes;
use App\Models\HR\PayrollItem;
use App\Models\HR\PayrollItemDetail;
use App\Models\HR\PayrollPeriod;
use App\Models\HR\SalaryComponent;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Payslip Detail')]
class ItemForm extends Component
{
    use WithNotes;

    public PayrollItem $item;
    public PayrollPeriod $period;

    // Adjustment form
    public array $details = [];
    public bool $showAddModal = false;
    public ?int $editingIndex = null;
    public string $adjustmentName = '';
    public string $adjustmentType = 'earning';
    public float $adjustmentAmount = 0;
    public string $adjustmentNotes = '';

    public function mount(int $periodId, int $itemId): void
    {
        $this->period = PayrollPeriod::findOrFail($periodId);
        $this->item = PayrollItem::with(['employee.department', 'employee.position', 'details'])
            ->where('payroll_period_id', $periodId)
            ->findOrFail($itemId);

        $this->loadDetails();
    }

    protected function loadDetails(): void
    {
        $this->details = $this->item->details()
            ->orderBy('type')
            ->orderBy('source')
            ->orderBy('component_name')
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'salary_component_id' => $d->salary_component_id,
                'component_name' => $d->component_name,
                'type' => $d->type,
                'source' => $d->source,
                'amount' => $d->amount,
                'notes' => $d->notes,
            ])
            ->toArray();
    }

    public function openAddModal(): void
    {
        $this->editingIndex = null;
        $this->adjustmentName = '';
        $this->adjustmentType = 'earning';
        $this->adjustmentAmount = 0;
        $this->adjustmentNotes = '';
        $this->showAddModal = true;
    }

    public function editDetail(int $index): void
    {
        $detail = $this->details[$index] ?? null;
        if (!$detail || $detail['source'] !== 'adjustment') return;

        $this->editingIndex = $index;
        $this->adjustmentName = $detail['component_name'];
        $this->adjustmentType = $detail['type'];
        $this->adjustmentAmount = $detail['amount'];
        $this->adjustmentNotes = $detail['notes'] ?? '';
        $this->showAddModal = true;
    }

    public function saveAdjustment(): void
    {
        $this->validate([
            'adjustmentName' => 'required|string|max:255',
            'adjustmentType' => 'required|in:earning,deduction',
            'adjustmentAmount' => 'required|numeric|min:0',
        ], [], [
            'adjustmentName' => 'name',
            'adjustmentType' => 'type',
            'adjustmentAmount' => 'amount',
        ]);

        $typeLabel = ucfirst($this->adjustmentType);
        $formattedAmount = 'Rp ' . number_format($this->adjustmentAmount, 0, ',', '.');

        if ($this->editingIndex !== null) {
            // Update existing
            $detailId = $this->details[$this->editingIndex]['id'];
            $oldDetail = $this->details[$this->editingIndex];
            
            PayrollItemDetail::where('id', $detailId)->update([
                'component_name' => $this->adjustmentName,
                'type' => $this->adjustmentType,
                'amount' => $this->adjustmentAmount,
                'notes' => $this->adjustmentNotes ?: null,
            ]);

            // Log activity
            activity()
                ->performedOn($this->item)
                ->causedBy(auth()->user())
                ->withProperties([
                    'action' => 'update_adjustment',
                    'component_name' => $this->adjustmentName,
                    'type' => $this->adjustmentType,
                    'amount' => $this->adjustmentAmount,
                    'old_amount' => $oldDetail['amount'],
                ])
                ->log("Updated {$typeLabel}: {$this->adjustmentName} → {$formattedAmount}");
        } else {
            // Create new
            PayrollItemDetail::create([
                'payroll_item_id' => $this->item->id,
                'salary_component_id' => null,
                'component_name' => $this->adjustmentName,
                'type' => $this->adjustmentType,
                'source' => 'adjustment',
                'amount' => $this->adjustmentAmount,
                'notes' => $this->adjustmentNotes ?: null,
            ]);

            // Log activity
            activity()
                ->performedOn($this->item)
                ->causedBy(auth()->user())
                ->withProperties([
                    'action' => 'add_adjustment',
                    'component_name' => $this->adjustmentName,
                    'type' => $this->adjustmentType,
                    'amount' => $this->adjustmentAmount,
                ])
                ->log("Added {$typeLabel}: {$this->adjustmentName} → {$formattedAmount}");
        }

        $this->item->recalculate();
        $this->item->period->recalculateTotals();
        $this->loadDetails();
        $this->showAddModal = false;
        $this->resetAdjustmentForm();

        session()->flash('success', $this->editingIndex !== null ? 'Adjustment updated.' : 'Adjustment added.');
    }

    public function removeDetail(int $index): void
    {
        $detail = $this->details[$index] ?? null;
        if (!$detail || $detail['source'] !== 'adjustment') return;

        $typeLabel = ucfirst($detail['type']);
        $formattedAmount = 'Rp ' . number_format($detail['amount'], 0, ',', '.');

        // Log activity before deleting
        activity()
            ->performedOn($this->item)
            ->causedBy(auth()->user())
            ->withProperties([
                'action' => 'remove_adjustment',
                'component_name' => $detail['component_name'],
                'type' => $detail['type'],
                'amount' => $detail['amount'],
            ])
            ->log("Removed {$typeLabel}: {$detail['component_name']} → {$formattedAmount}");

        PayrollItemDetail::where('id', $detail['id'])->delete();
        $this->item->recalculate();
        $this->item->period->recalculateTotals();
        $this->loadDetails();

        session()->flash('success', 'Adjustment removed.');
    }

    public function updateDetailAmount(int $index): void
    {
        $detail = $this->details[$index] ?? null;
        if (!$detail) return;

        $oldAmount = PayrollItemDetail::find($detail['id'])?->amount ?? 0;
        $newAmount = $detail['amount'];

        PayrollItemDetail::where('id', $detail['id'])->update([
            'amount' => $newAmount,
        ]);

        // Log activity if amount changed
        if ($oldAmount != $newAmount) {
            $typeLabel = ucfirst($detail['type']);
            $formattedOld = 'Rp ' . number_format($oldAmount, 0, ',', '.');
            $formattedNew = 'Rp ' . number_format($newAmount, 0, ',', '.');

            activity()
                ->performedOn($this->item)
                ->causedBy(auth()->user())
                ->withProperties([
                    'action' => 'update_amount',
                    'component_name' => $detail['component_name'],
                    'type' => $detail['type'],
                    'old_amount' => $oldAmount,
                    'new_amount' => $newAmount,
                ])
                ->log("Updated {$typeLabel}: {$detail['component_name']} {$formattedOld} → {$formattedNew}");
        }

        $this->item->recalculate();
        $this->item->period->recalculateTotals();
    }

    protected function resetAdjustmentForm(): void
    {
        $this->editingIndex = null;
        $this->adjustmentName = '';
        $this->adjustmentType = 'earning';
        $this->adjustmentAmount = 0;
        $this->adjustmentNotes = '';
    }

    #[Computed]
    public function totalEarnings(): float
    {
        return collect($this->details)->where('type', 'earning')->sum('amount');
    }

    #[Computed]
    public function totalDeductions(): float
    {
        return collect($this->details)->where('type', 'deduction')->sum('amount');
    }

    #[Computed]
    public function netSalary(): float
    {
        return $this->item->basic_salary + $this->totalEarnings - $this->totalDeductions;
    }

    #[Computed]
    public function isEditable(): bool
    {
        return $this->period->status === 'draft';
    }

    #[Computed]
    public function commonAdjustments(): array
    {
        return [
            ['name' => 'Overtime', 'type' => 'earning'],
            ['name' => 'Performance Bonus', 'type' => 'earning'],
            ['name' => 'Holiday Bonus', 'type' => 'earning'],
            ['name' => 'Referral Bonus', 'type' => 'earning'],
            ['name' => 'Reimbursement', 'type' => 'earning'],
            ['name' => 'Late Deduction', 'type' => 'deduction'],
            ['name' => 'Absent Deduction', 'type' => 'deduction'],
            ['name' => 'Loan Repayment', 'type' => 'deduction'],
            ['name' => 'Advance Salary', 'type' => 'deduction'],
            ['name' => 'Damage/Loss', 'type' => 'deduction'],
        ];
    }

    public function selectCommonAdjustment(string $name, string $type): void
    {
        $this->adjustmentName = $name;
        $this->adjustmentType = $type;
    }

    protected function getNotableModel()
    {
        return $this->item;
    }

    public function getActivities()
    {
        $activities = Activity::where('subject_type', PayrollItem::class)
            ->where('subject_id', $this->item->id)
            ->with('causer')
            ->latest()
            ->limit(20)
            ->get()
            ->map(function ($activity) {
                $activity->type = 'activity';
                return $activity;
            });

        if (method_exists($this->item, 'notes')) {
            $notes = $this->item->notes()
                ->with('user')
                ->latest()
                ->limit(20)
                ->get()
                ->map(function ($note) {
                    $note->type = 'note';
                    $note->causer = $note->user;
                    return $note;
                });

            return $activities->concat($notes)
                ->sortByDesc('created_at')
                ->values()
                ->take(30);
        }

        return $activities;
    }

    public function render()
    {
        return view('livewire.hr.payroll.item-form', [
            'activities' => $this->getActivities(),
            'itemCreatedAt' => $this->item->created_at?->format('H:i'),
        ]);
    }
}
