<?php

namespace App\Livewire\HR\Payroll;

use App\Livewire\Concerns\WithNotes;
use App\Models\HR\Employee;
use App\Models\HR\PayrollItem;
use App\Models\HR\PayrollItemDetail;
use App\Models\HR\PayrollPeriod;
use App\Models\HR\SalaryComponent;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Payroll')]
class Form extends Component
{
    use WithNotes, WithPagination;

    public ?int $periodId = null;
    public ?PayrollPeriod $period = null;

    public string $name = '';
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $paymentDate = null;
    public string $status = 'draft';
    public string $notes = '';

    // Payslip search & pagination
    #[Url(as: 'q')]
    public string $payslipSearch = '';
    public int $perPage = 10;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'paymentDate' => 'nullable|date',
            'notes' => 'nullable|string',
        ];
    }

    public function mount(?int $id = null): void
    {
        $this->periodId = $id;

        if ($id) {
            $this->period = PayrollPeriod::with(['items.employee', 'items.details'])->findOrFail($id);
            $this->name = $this->period->name;
            $this->startDate = $this->period->start_date->format('Y-m-d');
            $this->endDate = $this->period->end_date->format('Y-m-d');
            $this->paymentDate = $this->period->payment_date?->format('Y-m-d');
            $this->status = $this->period->status;
            $this->notes = $this->period->notes ?? '';
        } else {
            $this->name = now()->format('F Y');
            $this->startDate = now()->startOfMonth()->format('Y-m-d');
            $this->endDate = now()->endOfMonth()->format('Y-m-d');
            $this->paymentDate = now()->endOfMonth()->format('Y-m-d');
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'payment_date' => $this->paymentDate,
            'notes' => $this->notes ?: null,
        ];

        if ($this->periodId) {
            $this->period->update($data);
            session()->flash('success', 'Payroll period updated.');
        } else {
            $data['created_by'] = auth()->id();
            $this->period = PayrollPeriod::create($data);
            $this->periodId = $this->period->id;
            session()->flash('success', 'Payroll period created.');
            $this->redirect(route('hr.payroll.edit', $this->periodId), navigate: true);
        }
    }

    public function generatePayroll(): void
    {
        if (!$this->period || $this->period->status !== 'draft') return;

        $employees = Employee::where('status', 'active')
            ->with(['employeeSalaryComponents.salaryComponent'])
            ->get();

        $generatedCount = 0;

        foreach ($employees as $employee) {
            $existing = PayrollItem::where('payroll_period_id', $this->period->id)
                ->where('employee_id', $employee->id)
                ->first();

            if (!$existing) {
                // Create payroll item
                $payrollItem = PayrollItem::create([
                    'payroll_period_id' => $this->period->id,
                    'employee_id' => $employee->id,
                    'basic_salary' => $employee->basic_salary ?? 0,
                    'total_earnings' => 0,
                    'total_deductions' => 0,
                    'net_salary' => $employee->basic_salary ?? 0,
                    'working_days' => 22,
                    'days_worked' => 22,
                ]);

                // Copy active salary components as details
                foreach ($employee->employeeSalaryComponents as $empComponent) {
                    if (!$empComponent->is_active) continue;
                    
                    // Check if component is within effective date range
                    $periodStart = $this->period->start_date;
                    $periodEnd = $this->period->end_date;
                    
                    if ($empComponent->effective_from && $empComponent->effective_from > $periodEnd) continue;
                    if ($empComponent->effective_to && $empComponent->effective_to < $periodStart) continue;

                    PayrollItemDetail::create([
                        'payroll_item_id' => $payrollItem->id,
                        'salary_component_id' => $empComponent->salary_component_id,
                        'component_name' => $empComponent->salaryComponent->name,
                        'type' => $empComponent->salaryComponent->type,
                        'source' => 'component',
                        'amount' => $empComponent->amount,
                        'notes' => null,
                    ]);
                }

                // Recalculate totals
                $payrollItem->recalculate();
                $generatedCount++;
            }
        }

        $this->period->recalculateTotals();
        session()->flash('success', "Payroll generated for {$generatedCount} employees.");
    }

    public function approve(): void
    {
        if (!$this->period || $this->period->status !== 'draft') return;

        $this->period->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $this->status = 'approved';
        session()->flash('success', 'Payroll approved.');
    }

    public function startProcessing(): void
    {
        if (!$this->period || $this->period->status !== 'approved') return;

        $this->period->update(['status' => 'processing']);
        $this->status = 'processing';
        session()->flash('success', 'Payroll processing started.');
    }

    public function markAsPaid(): void
    {
        if (!$this->period || $this->period->status !== 'processing') return;

        $this->period->update([
            'status' => 'paid',
            'payment_date' => $this->paymentDate ?? now()->format('Y-m-d'),
        ]);
        $this->status = 'paid';
        session()->flash('success', 'Payroll marked as paid.');
    }

    public function cancel(): void
    {
        if (!$this->period || in_array($this->period->status, ['paid', 'cancelled'])) return;

        $this->period->update(['status' => 'cancelled']);
        $this->status = 'cancelled';
        session()->flash('success', 'Payroll cancelled.');
    }

    public function resetToDraft(): void
    {
        if (!$this->period || !in_array($this->period->status, ['approved', 'cancelled'])) return;

        $this->period->update([
            'status' => 'draft',
            'approved_by' => null,
            'approved_at' => null,
        ]);
        $this->status = 'draft';
        session()->flash('success', 'Payroll reset to draft.');
    }

    public function delete(): void
    {
        if (!$this->period) return;

        $this->period->delete();
        session()->flash('success', 'Payroll period deleted.');
        $this->redirect(route('hr.payroll.index'), navigate: true);
    }

    protected function getNotableModel()
    {
        return $this->period;
    }

    public function getActivities()
    {
        if (!$this->periodId) {
            return collect();
        }

        $modelClass = PayrollPeriod::class;

        // Get activity logs from custom activity_logs table
        $activities = DB::table('activity_logs')
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->where('activity_logs.model_type', $modelClass)
            ->where('activity_logs.model_id', $this->periodId)
            ->select('activity_logs.*', 'users.name as causer_name')
            ->orderByDesc('activity_logs.created_at')
            ->limit(20)
            ->get()
            ->map(fn($activity) => (object) [
                'id' => $activity->id,
                'type' => 'activity',
                'action' => $activity->action,
                'description' => $activity->description,
                'properties' => json_decode($activity->properties ?? '{}', true),
                'causer' => (object) ['name' => $activity->causer_name ?? $activity->user_name ?? 'System'],
                'created_at' => \Carbon\Carbon::parse($activity->created_at),
            ]);

        if ($this->period && method_exists($this->period, 'notes')) {
            $notes = $this->period->notes()
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

    public function updatedPayslipSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $itemsQuery = $this->period
            ? $this->period->items()
                ->with(['employee.department', 'employee.position'])
                ->when($this->payslipSearch, function ($query) {
                    $query->whereHas('employee', function ($q) {
                        $q->where('name', 'ilike', '%' . $this->payslipSearch . '%')
                          ->orWhere('email', 'ilike', '%' . $this->payslipSearch . '%');
                    })
                    ->orWhereHas('employee.department', function ($q) {
                        $q->where('name', 'ilike', '%' . $this->payslipSearch . '%');
                    });
                })
            : null;

        $items = $itemsQuery?->paginate($this->perPage) ?? collect();
        
        // Calculate totals for all items (not just current page)
        $allItems = $this->period?->items ?? collect();

        return view('livewire.hr.payroll.form', [
            'items' => $items,
            'totalBasicSalary' => $allItems->sum('basic_salary'),
            'totalEarnings' => $allItems->sum('total_earnings'),
            'totalDeductions' => $allItems->sum('total_deductions'),
            'totalNetSalary' => $allItems->sum('net_salary'),
            'totalItemsCount' => $allItems->count(),
            'activities' => $this->getActivities(),
            'periodCreatedAt' => $this->period?->created_at?->format('H:i'),
        ]);
    }
}
