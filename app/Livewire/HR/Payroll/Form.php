<?php

namespace App\Livewire\HR\Payroll;

use App\Livewire\Concerns\WithNotes;
use App\Models\HR\Employee;
use App\Models\HR\PayrollItem;
use App\Models\HR\PayrollPeriod;
use App\Models\HR\SalaryComponent;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Payroll')]
class Form extends Component
{
    use WithNotes;

    public ?int $periodId = null;
    public ?PayrollPeriod $period = null;

    public string $name = '';
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $paymentDate = null;
    public string $status = 'draft';
    public string $notes = '';

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

        $employees = Employee::where('status', 'active')->get();

        foreach ($employees as $employee) {
            $existing = PayrollItem::where('payroll_period_id', $this->period->id)
                ->where('employee_id', $employee->id)
                ->first();

            if (!$existing) {
                PayrollItem::create([
                    'payroll_period_id' => $this->period->id,
                    'employee_id' => $employee->id,
                    'basic_salary' => $employee->basic_salary,
                    'total_earnings' => 0,
                    'total_deductions' => 0,
                    'net_salary' => $employee->basic_salary,
                    'working_days' => 22,
                    'days_worked' => 22,
                ]);
            }
        }

        $this->period->recalculateTotals();
        session()->flash('success', 'Payroll generated for ' . $employees->count() . ' employees.');
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

        $activities = Activity::where('subject_type', PayrollPeriod::class)
            ->where('subject_id', $this->periodId)
            ->with('causer')
            ->latest()
            ->limit(20)
            ->get()
            ->map(function ($activity) {
                $activity->type = 'activity';
                return $activity;
            });

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

    public function render()
    {
        return view('livewire.hr.payroll.form', [
            'items' => $this->period?->items()->with(['employee.department', 'employee.position'])->get() ?? collect(),
            'activities' => $this->getActivities(),
            'periodCreatedAt' => $this->period?->created_at?->format('H:i'),
        ]);
    }
}
