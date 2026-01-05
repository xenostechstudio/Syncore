<?php

namespace App\Livewire\HR\Employees;

use App\Livewire\Concerns\WithNotes;
use App\Models\HR\Department;
use App\Models\HR\Employee;
use App\Models\HR\EmployeeSalaryComponent;
use App\Models\HR\Position;
use App\Models\HR\SalaryComponent;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Employee')]
class Form extends Component
{
    use WithNotes;

    public ?int $employeeId = null;
    public ?Employee $employee = null;

    // Timestamps
    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    protected function getNotableModel()
    {
        return $this->employeeId ? Employee::find($this->employeeId) : null;
    }

    public function getActivitiesAndNotesProperty(): \Illuminate\Support\Collection
    {
        if (!$this->employeeId) {
            return collect();
        }

        $employee = Employee::find($this->employeeId);
        
        $activities = Activity::where('subject_type', Employee::class)
            ->where('subject_id', $this->employeeId)
            ->with('causer')
            ->get()
            ->map(function ($activity) {
                return [
                    'type' => 'activity',
                    'data' => $activity,
                    'created_at' => $activity->created_at,
                ];
            });

        $notes = $employee->notes()->with('user')->get()->map(function ($note) {
            return [
                'type' => 'note',
                'data' => $note,
                'created_at' => $note->created_at,
            ];
        });

        return $activities->concat($notes)
            ->sortByDesc('created_at')
            ->take(30)
            ->values();
    }

    // Basic Info
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $mobile = '';

    // Work Info
    public ?int $departmentId = null;
    public ?int $positionId = null;
    public ?int $managerId = null;
    public ?string $hireDate = null;
    public ?string $contractEndDate = null;
    public string $employmentType = 'permanent';
    public string $status = 'active';

    // Personal Info
    public ?string $birthDate = null;
    public string $gender = '';
    public string $maritalStatus = '';
    public string $nationality = '';
    public string $idNumber = '';
    public string $address = '';
    public string $city = '';
    public string $postalCode = '';
    public string $emergencyContactName = '';
    public string $emergencyContactPhone = '';
    public string $emergencyContactRelation = '';

    // Payroll
    public float $basicSalary = 0;
    public string $bankName = '';
    public string $bankAccountNumber = '';
    public string $bankAccountName = '';
    public string $taxId = '';

    // Settings
    public ?int $userId = null;
    public ?int $hrResponsibleId = null;
    public string $pinCode = '';

    public string $notes = '';

    // Create User Modal
    public bool $showCreateUserModal = false;
    public string $newUserName = '';
    public string $newUserEmail = '';
    public string $newUserPassword = '';

    // Salary Components
    public array $employeeSalaryComponents = [];

    public function openCreateUserModal(): void
    {
        $this->newUserName = $this->name;
        $this->newUserEmail = $this->email;
        $this->newUserPassword = '';
        $this->showCreateUserModal = true;
    }

    public function createUser(): void
    {
        $this->validate([
            'newUserName' => 'required|string|max:255',
            'newUserEmail' => 'required|email|unique:users,email',
            'newUserPassword' => 'required|string|min:8',
        ], [], [
            'newUserName' => 'name',
            'newUserEmail' => 'email',
            'newUserPassword' => 'password',
        ]);

        $user = User::create([
            'name' => $this->newUserName,
            'email' => $this->newUserEmail,
            'password' => bcrypt($this->newUserPassword),
        ]);

        $this->userId = $user->id;
        $this->showCreateUserModal = false;
        $this->reset(['newUserName', 'newUserEmail', 'newUserPassword']);
        
        session()->flash('success', 'User created and linked successfully.');
    }

    // Salary Component Methods
    public function addComponent(): void
    {
        $this->employeeSalaryComponents[] = [
            'id' => null,
            'salary_component_id' => null,
            'component_name' => '',
            'component_code' => '',
            'component_type' => 'earning',
            'amount' => 0,
            'effective_from' => now()->format('Y-m-d'),
            'effective_to' => null,
            'is_active' => true,
        ];
    }

    public function selectComponent(int $index, int $componentId): void
    {
        $salaryComponent = SalaryComponent::find($componentId);
        if (!$salaryComponent) return;

        // Check if component already exists (except current index)
        foreach ($this->employeeSalaryComponents as $i => $existing) {
            if ($i !== $index && $existing['salary_component_id'] == $componentId) {
                session()->flash('error', 'This salary component is already added.');
                return;
            }
        }

        $this->employeeSalaryComponents[$index]['salary_component_id'] = $componentId;
        $this->employeeSalaryComponents[$index]['component_name'] = $salaryComponent->name;
        $this->employeeSalaryComponents[$index]['component_code'] = $salaryComponent->code;
        $this->employeeSalaryComponents[$index]['component_type'] = $salaryComponent->type;
        $this->employeeSalaryComponents[$index]['amount'] = $salaryComponent->default_amount ?? 0;
    }

    public function removeComponent(int $index): void
    {
        unset($this->employeeSalaryComponents[$index]);
        $this->employeeSalaryComponents = array_values($this->employeeSalaryComponents);
    }

    #[Computed]
    public function totalEarnings(): float
    {
        return collect($this->employeeSalaryComponents)
            ->where('component_type', 'earning')
            ->sum('amount');
    }

    #[Computed]
    public function totalDeductions(): float
    {
        return collect($this->employeeSalaryComponents)
            ->where('component_type', 'deduction')
            ->sum('amount');
    }

    #[Computed]
    public function estimatedTotalSalary(): float
    {
        return $this->basicSalary + $this->totalEarnings - $this->totalDeductions;
    }

    #[Computed]
    public function availableSalaryComponents(): \Illuminate\Database\Eloquent\Collection
    {
        return SalaryComponent::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
    }

    protected function loadEmployeeSalaryComponents(): void
    {
        if (!$this->employeeId) {
            $this->employeeSalaryComponents = [];
            return;
        }

        $this->employeeSalaryComponents = EmployeeSalaryComponent::where('employee_id', $this->employeeId)
            ->with('salaryComponent')
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'salary_component_id' => $item->salary_component_id,
                'component_name' => $item->salaryComponent->name,
                'component_code' => $item->salaryComponent->code,
                'component_type' => $item->salaryComponent->type,
                'amount' => $item->amount,
                'effective_from' => $item->effective_from?->format('Y-m-d'),
                'effective_to' => $item->effective_to?->format('Y-m-d'),
                'is_active' => $item->is_active,
            ])
            ->toArray();
    }

    protected function saveEmployeeSalaryComponents(): void
    {
        if (!$this->employeeId) return;

        $existingIds = collect($this->employeeSalaryComponents)
            ->pluck('id')
            ->filter()
            ->toArray();

        // Delete removed components
        EmployeeSalaryComponent::where('employee_id', $this->employeeId)
            ->whereNotIn('id', $existingIds)
            ->delete();

        // Update or create components
        foreach ($this->employeeSalaryComponents as $component) {
            EmployeeSalaryComponent::updateOrCreate(
                ['id' => $component['id'] ?? null],
                [
                    'employee_id' => $this->employeeId,
                    'salary_component_id' => $component['salary_component_id'],
                    'amount' => $component['amount'],
                    'effective_from' => $component['effective_from'],
                    'effective_to' => $component['effective_to'],
                    'is_active' => $component['is_active'],
                ]
            );
        }
    }

    #[Computed]
    public function positions(): \Illuminate\Database\Eloquent\Collection
    {
        if ($this->departmentId) {
            return Position::where('department_id', $this->departmentId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }
        return Position::where('is_active', true)->orderBy('name')->get();
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'birthDate' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'maritalStatus' => 'nullable|in:single,married,divorced,widowed',
            'nationality' => 'nullable|string|max:100',
            'idNumber' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'postalCode' => 'nullable|string|max:20',
            'emergencyContactName' => 'nullable|string|max:255',
            'emergencyContactPhone' => 'nullable|string|max:50',
            'emergencyContactRelation' => 'nullable|string|max:100',
            'departmentId' => 'nullable|exists:departments,id',
            'positionId' => 'nullable|exists:positions,id',
            'managerId' => 'nullable|exists:employees,id',
            'hireDate' => 'nullable|date',
            'contractEndDate' => 'nullable|date|after_or_equal:hireDate',
            'employmentType' => 'required|in:permanent,contract,probation,intern,freelance',
            'status' => 'required|in:active,inactive,terminated,resigned',
            'basicSalary' => 'required|numeric|min:0',
            'bankName' => 'nullable|string|max:100',
            'bankAccountNumber' => 'nullable|string|max:50',
            'bankAccountName' => 'nullable|string|max:255',
            'taxId' => 'nullable|string|max:50',
            'userId' => 'nullable|exists:users,id',
            'hrResponsibleId' => 'nullable|exists:employees,id',
            'pinCode' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
        ];
    }

    public function mount(?int $id = null): void
    {
        $this->employeeId = $id;

        if ($id) {
            $this->employee = Employee::with(['department', 'position', 'manager'])->findOrFail($id);
            $this->name = $this->employee->name;
            $this->email = $this->employee->email ?? '';
            $this->phone = $this->employee->phone ?? '';
            $this->mobile = $this->employee->mobile ?? '';
            $this->birthDate = $this->employee->birth_date?->format('Y-m-d');
            $this->gender = $this->employee->gender ?? '';
            $this->maritalStatus = $this->employee->marital_status ?? '';
            $this->nationality = $this->employee->nationality ?? '';
            $this->idNumber = $this->employee->id_number ?? '';
            $this->address = $this->employee->address ?? '';
            $this->city = $this->employee->city ?? '';
            $this->postalCode = $this->employee->postal_code ?? '';
            $this->emergencyContactName = $this->employee->emergency_contact_name ?? '';
            $this->emergencyContactPhone = $this->employee->emergency_contact_phone ?? '';
            $this->emergencyContactRelation = $this->employee->emergency_contact_relation ?? '';
            $this->departmentId = $this->employee->department_id;
            $this->positionId = $this->employee->position_id;
            $this->managerId = $this->employee->manager_id;
            $this->hireDate = $this->employee->hire_date?->format('Y-m-d');
            $this->contractEndDate = $this->employee->contract_end_date?->format('Y-m-d');
            $this->employmentType = $this->employee->employment_type;
            $this->status = $this->employee->status;
            $this->basicSalary = $this->employee->basic_salary;
            $this->bankName = $this->employee->bank_name ?? '';
            $this->bankAccountNumber = $this->employee->bank_account_number ?? '';
            $this->bankAccountName = $this->employee->bank_account_name ?? '';
            $this->taxId = $this->employee->tax_id ?? '';
            $this->userId = $this->employee->user_id;
            $this->hrResponsibleId = $this->employee->hr_responsible_id;
            $this->pinCode = $this->employee->pin_code ?? '';
            $this->notes = $this->employee->notes ?? '';
            $this->createdAt = $this->employee->created_at->format('M d, Y \a\t H:i');
            $this->updatedAt = $this->employee->updated_at->format('M d, Y \a\t H:i');
            
            $this->loadEmployeeSalaryComponents();
        } else {
            $this->hireDate = now()->format('Y-m-d');
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'mobile' => $this->mobile ?: null,
            'birth_date' => $this->birthDate ?: null,
            'gender' => $this->gender ?: null,
            'marital_status' => $this->maritalStatus ?: null,
            'nationality' => $this->nationality ?: null,
            'id_number' => $this->idNumber ?: null,
            'address' => $this->address ?: null,
            'city' => $this->city ?: null,
            'postal_code' => $this->postalCode ?: null,
            'emergency_contact_name' => $this->emergencyContactName ?: null,
            'emergency_contact_phone' => $this->emergencyContactPhone ?: null,
            'emergency_contact_relation' => $this->emergencyContactRelation ?: null,
            'department_id' => $this->departmentId,
            'position_id' => $this->positionId,
            'manager_id' => $this->managerId,
            'hire_date' => $this->hireDate ?: null,
            'contract_end_date' => $this->contractEndDate ?: null,
            'employment_type' => $this->employmentType,
            'status' => $this->status,
            'basic_salary' => $this->basicSalary,
            'bank_name' => $this->bankName ?: null,
            'bank_account_number' => $this->bankAccountNumber ?: null,
            'bank_account_name' => $this->bankAccountName ?: null,
            'tax_id' => $this->taxId ?: null,
            'user_id' => $this->userId,
            'hr_responsible_id' => $this->hrResponsibleId,
            'pin_code' => $this->pinCode ?: null,
            'notes' => $this->notes ?: null,
        ];

        if ($this->employeeId) {
            $this->employee->update($data);
            $this->saveEmployeeSalaryComponents();
            session()->flash('success', 'Employee updated successfully.');
        } else {
            $employee = Employee::create($data);
            $this->employeeId = $employee->id;
            $this->employee = $employee;
            $this->saveEmployeeSalaryComponents();
            session()->flash('success', 'Employee created successfully.');
        }
    }

    public function delete(): void
    {
        if (!$this->employee) return;

        $this->employee->delete();
        session()->flash('success', 'Employee deleted successfully.');
        $this->redirect(route('hr.employees.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.hr.employees.form', [
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'employees' => Employee::where('id', '!=', $this->employeeId ?? 0)
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'users' => User::orderBy('name')->get(),
            'activities' => $this->activitiesAndNotes,
        ]);
    }
}
