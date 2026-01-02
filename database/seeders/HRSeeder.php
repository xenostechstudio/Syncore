<?php

namespace Database\Seeders;

use App\Models\HR\Department;
use App\Models\HR\Employee;
use App\Models\HR\LeaveType;
use App\Models\HR\Position;
use App\Models\HR\SalaryComponent;
use Illuminate\Database\Seeder;

class HRSeeder extends Seeder
{
    public function run(): void
    {
        // Create Departments
        $departments = [
            ['name' => 'Executive', 'code' => 'EXEC', 'description' => 'Executive Management', 'is_active' => true],
            ['name' => 'Human Resources', 'code' => 'HR', 'description' => 'Human Resources Department', 'is_active' => true],
            ['name' => 'Finance', 'code' => 'FIN', 'description' => 'Finance & Accounting', 'is_active' => true],
            ['name' => 'Information Technology', 'code' => 'IT', 'description' => 'IT Department', 'is_active' => true],
            ['name' => 'Sales', 'code' => 'SALES', 'description' => 'Sales Department', 'is_active' => true],
            ['name' => 'Marketing', 'code' => 'MKT', 'description' => 'Marketing Department', 'is_active' => true],
            ['name' => 'Operations', 'code' => 'OPS', 'description' => 'Operations Department', 'is_active' => true],
            ['name' => 'Customer Service', 'code' => 'CS', 'description' => 'Customer Service Department', 'is_active' => true],
        ];

        $deptModels = [];
        foreach ($departments as $dept) {
            $deptModels[$dept['code']] = Department::firstOrCreate(['code' => $dept['code']], $dept);
        }

        // Create Positions
        $positions = [
            ['name' => 'CEO', 'department_id' => $deptModels['EXEC']->id, 'description' => 'Chief Executive Officer', 'is_active' => true],
            ['name' => 'COO', 'department_id' => $deptModels['EXEC']->id, 'description' => 'Chief Operating Officer', 'is_active' => true],
            ['name' => 'CFO', 'department_id' => $deptModels['FIN']->id, 'description' => 'Chief Financial Officer', 'is_active' => true],
            ['name' => 'CTO', 'department_id' => $deptModels['IT']->id, 'description' => 'Chief Technology Officer', 'is_active' => true],
            ['name' => 'HR Manager', 'department_id' => $deptModels['HR']->id, 'description' => 'Human Resources Manager', 'is_active' => true],
            ['name' => 'HR Officer', 'department_id' => $deptModels['HR']->id, 'description' => 'Human Resources Officer', 'is_active' => true],
            ['name' => 'Finance Manager', 'department_id' => $deptModels['FIN']->id, 'description' => 'Finance Manager', 'is_active' => true],
            ['name' => 'Accountant', 'department_id' => $deptModels['FIN']->id, 'description' => 'Accountant', 'is_active' => true],
            ['name' => 'IT Manager', 'department_id' => $deptModels['IT']->id, 'description' => 'IT Manager', 'is_active' => true],
            ['name' => 'Software Developer', 'department_id' => $deptModels['IT']->id, 'description' => 'Software Developer', 'is_active' => true],
            ['name' => 'System Administrator', 'department_id' => $deptModels['IT']->id, 'description' => 'System Administrator', 'is_active' => true],
            ['name' => 'Sales Manager', 'department_id' => $deptModels['SALES']->id, 'description' => 'Sales Manager', 'is_active' => true],
            ['name' => 'Sales Executive', 'department_id' => $deptModels['SALES']->id, 'description' => 'Sales Executive', 'is_active' => true],
            ['name' => 'Marketing Manager', 'department_id' => $deptModels['MKT']->id, 'description' => 'Marketing Manager', 'is_active' => true],
            ['name' => 'Marketing Specialist', 'department_id' => $deptModels['MKT']->id, 'description' => 'Marketing Specialist', 'is_active' => true],
            ['name' => 'Operations Manager', 'department_id' => $deptModels['OPS']->id, 'description' => 'Operations Manager', 'is_active' => true],
            ['name' => 'Warehouse Staff', 'department_id' => $deptModels['OPS']->id, 'description' => 'Warehouse Staff', 'is_active' => true],
            ['name' => 'Customer Service Manager', 'department_id' => $deptModels['CS']->id, 'description' => 'Customer Service Manager', 'is_active' => true],
            ['name' => 'Customer Service Rep', 'department_id' => $deptModels['CS']->id, 'description' => 'Customer Service Representative', 'is_active' => true],
        ];

        $posModels = [];
        foreach ($positions as $pos) {
            $posModels[$pos['name']] = Position::firstOrCreate(
                ['name' => $pos['name'], 'department_id' => $pos['department_id']],
                $pos
            );
        }

        // Create Employees
        $employees = [
            [
                'name' => 'Ahmad Wijaya',
                'email' => 'ahmad.wijaya@company.com',
                'phone' => '+62 812 1234 5678',
                'department_id' => $deptModels['EXEC']->id,
                'position_id' => $posModels['CEO']->id,
                'employment_type' => 'permanent',
                'status' => 'active',
                'hire_date' => '2020-01-15',
                'basic_salary' => 50000000,
                'gender' => 'male',
            ],
            [
                'name' => 'Siti Rahayu',
                'email' => 'siti.rahayu@company.com',
                'phone' => '+62 813 2345 6789',
                'department_id' => $deptModels['HR']->id,
                'position_id' => $posModels['HR Manager']->id,
                'employment_type' => 'permanent',
                'status' => 'active',
                'hire_date' => '2020-03-01',
                'basic_salary' => 25000000,
                'gender' => 'female',
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@company.com',
                'phone' => '+62 814 3456 7890',
                'department_id' => $deptModels['IT']->id,
                'position_id' => $posModels['IT Manager']->id,
                'employment_type' => 'permanent',
                'status' => 'active',
                'hire_date' => '2020-06-15',
                'basic_salary' => 28000000,
                'gender' => 'male',
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@company.com',
                'phone' => '+62 815 4567 8901',
                'department_id' => $deptModels['FIN']->id,
                'position_id' => $posModels['Finance Manager']->id,
                'employment_type' => 'permanent',
                'status' => 'active',
                'hire_date' => '2020-04-01',
                'basic_salary' => 26000000,
                'gender' => 'female',
            ],
            [
                'name' => 'Eko Prasetyo',
                'email' => 'eko.prasetyo@company.com',
                'phone' => '+62 816 5678 9012',
                'department_id' => $deptModels['SALES']->id,
                'position_id' => $posModels['Sales Manager']->id,
                'employment_type' => 'permanent',
                'status' => 'active',
                'hire_date' => '2021-01-10',
                'basic_salary' => 22000000,
                'gender' => 'male',
            ],
            [
                'name' => 'Fitri Handayani',
                'email' => 'fitri.handayani@company.com',
                'phone' => '+62 817 6789 0123',
                'department_id' => $deptModels['IT']->id,
                'position_id' => $posModels['Software Developer']->id,
                'employment_type' => 'permanent',
                'status' => 'active',
                'hire_date' => '2021-03-15',
                'basic_salary' => 18000000,
                'gender' => 'female',
            ],
            [
                'name' => 'Gunawan Setiawan',
                'email' => 'gunawan.setiawan@company.com',
                'phone' => '+62 818 7890 1234',
                'department_id' => $deptModels['IT']->id,
                'position_id' => $posModels['Software Developer']->id,
                'employment_type' => 'contract',
                'status' => 'active',
                'hire_date' => '2022-06-01',
                'contract_end_date' => '2025-05-31',
                'basic_salary' => 15000000,
                'gender' => 'male',
            ],
            [
                'name' => 'Hana Permata',
                'email' => 'hana.permata@company.com',
                'phone' => '+62 819 8901 2345',
                'department_id' => $deptModels['MKT']->id,
                'position_id' => $posModels['Marketing Manager']->id,
                'employment_type' => 'permanent',
                'status' => 'active',
                'hire_date' => '2021-08-01',
                'basic_salary' => 20000000,
                'gender' => 'female',
            ],
            [
                'name' => 'Irfan Hakim',
                'email' => 'irfan.hakim@company.com',
                'phone' => '+62 821 9012 3456',
                'department_id' => $deptModels['SALES']->id,
                'position_id' => $posModels['Sales Executive']->id,
                'employment_type' => 'permanent',
                'status' => 'active',
                'hire_date' => '2022-02-15',
                'basic_salary' => 12000000,
                'gender' => 'male',
            ],
            [
                'name' => 'Joko Widodo',
                'email' => 'joko.widodo@company.com',
                'phone' => '+62 822 0123 4567',
                'department_id' => $deptModels['OPS']->id,
                'position_id' => $posModels['Operations Manager']->id,
                'employment_type' => 'permanent',
                'status' => 'active',
                'hire_date' => '2020-09-01',
                'basic_salary' => 20000000,
                'gender' => 'male',
            ],
            [
                'name' => 'Kartika Sari',
                'email' => 'kartika.sari@company.com',
                'phone' => '+62 823 1234 5678',
                'department_id' => $deptModels['HR']->id,
                'position_id' => $posModels['HR Officer']->id,
                'employment_type' => 'permanent',
                'status' => 'active',
                'hire_date' => '2022-04-01',
                'basic_salary' => 10000000,
                'gender' => 'female',
            ],
            [
                'name' => 'Lukman Hakim',
                'email' => 'lukman.hakim@company.com',
                'phone' => '+62 824 2345 6789',
                'department_id' => $deptModels['FIN']->id,
                'position_id' => $posModels['Accountant']->id,
                'employment_type' => 'permanent',
                'status' => 'inactive',
                'hire_date' => '2021-05-15',
                'basic_salary' => 12000000,
                'gender' => 'male',
            ],
            [
                'name' => 'Maya Putri',
                'email' => 'maya.putri@company.com',
                'phone' => '+62 825 3456 7890',
                'department_id' => $deptModels['CS']->id,
                'position_id' => $posModels['Customer Service Manager']->id,
                'employment_type' => 'permanent',
                'status' => 'active',
                'hire_date' => '2021-07-01',
                'basic_salary' => 18000000,
                'gender' => 'female',
            ],
            [
                'name' => 'Nadia Kusuma',
                'email' => 'nadia.kusuma@company.com',
                'phone' => '+62 826 4567 8901',
                'department_id' => $deptModels['CS']->id,
                'position_id' => $posModels['Customer Service Rep']->id,
                'employment_type' => 'contract',
                'status' => 'active',
                'hire_date' => '2023-01-15',
                'contract_end_date' => '2025-01-14',
                'basic_salary' => 8000000,
                'gender' => 'female',
            ],
            [
                'name' => 'Oscar Pratama',
                'email' => 'oscar.pratama@company.com',
                'phone' => '+62 827 5678 9012',
                'department_id' => $deptModels['OPS']->id,
                'position_id' => $posModels['Warehouse Staff']->id,
                'employment_type' => 'permanent',
                'status' => 'resigned',
                'hire_date' => '2021-11-01',
                'basic_salary' => 7000000,
                'gender' => 'male',
            ],
            [
                'name' => 'Putri Anggraini',
                'email' => 'putri.anggraini@company.com',
                'phone' => '+62 828 6789 0123',
                'department_id' => $deptModels['MKT']->id,
                'position_id' => $posModels['Marketing Specialist']->id,
                'employment_type' => 'probation',
                'status' => 'active',
                'hire_date' => '2024-10-01',
                'basic_salary' => 9000000,
                'gender' => 'female',
            ],
            [
                'name' => 'Rudi Hermawan',
                'email' => 'rudi.hermawan@company.com',
                'phone' => '+62 829 7890 1234',
                'department_id' => $deptModels['IT']->id,
                'position_id' => $posModels['System Administrator']->id,
                'employment_type' => 'permanent',
                'status' => 'active',
                'hire_date' => '2022-08-15',
                'basic_salary' => 16000000,
                'gender' => 'male',
            ],
            [
                'name' => 'Sandra Dewi',
                'email' => 'sandra.dewi@company.com',
                'phone' => '+62 831 8901 2345',
                'department_id' => $deptModels['SALES']->id,
                'position_id' => $posModels['Sales Executive']->id,
                'employment_type' => 'permanent',
                'status' => 'terminated',
                'hire_date' => '2022-03-01',
                'basic_salary' => 11000000,
                'gender' => 'female',
            ],
            [
                'name' => 'Tommy Kurniawan',
                'email' => 'tommy.kurniawan@company.com',
                'phone' => '+62 832 9012 3456',
                'department_id' => $deptModels['OPS']->id,
                'position_id' => $posModels['Warehouse Staff']->id,
                'employment_type' => 'permanent',
                'status' => 'active',
                'hire_date' => '2023-05-15',
                'basic_salary' => 7500000,
                'gender' => 'male',
            ],
            [
                'name' => 'Umi Kalsum',
                'email' => 'umi.kalsum@company.com',
                'phone' => '+62 833 0123 4567',
                'department_id' => $deptModels['FIN']->id,
                'position_id' => $posModels['Accountant']->id,
                'employment_type' => 'intern',
                'status' => 'active',
                'hire_date' => '2024-09-01',
                'basic_salary' => 4000000,
                'gender' => 'female',
            ],
        ];

        foreach ($employees as $empData) {
            Employee::firstOrCreate(
                ['email' => $empData['email']],
                $empData
            );
        }

        // Set managers
        $ceo = Employee::where('email', 'ahmad.wijaya@company.com')->first();
        $hrManager = Employee::where('email', 'siti.rahayu@company.com')->first();
        $itManager = Employee::where('email', 'budi.santoso@company.com')->first();
        $salesManager = Employee::where('email', 'eko.prasetyo@company.com')->first();
        $opsManager = Employee::where('email', 'joko.widodo@company.com')->first();
        $csManager = Employee::where('email', 'maya.putri@company.com')->first();

        Employee::where('email', 'kartika.sari@company.com')->update(['manager_id' => $hrManager?->id]);
        Employee::whereIn('email', ['fitri.handayani@company.com', 'gunawan.setiawan@company.com', 'rudi.hermawan@company.com'])->update(['manager_id' => $itManager?->id]);
        Employee::whereIn('email', ['irfan.hakim@company.com', 'sandra.dewi@company.com'])->update(['manager_id' => $salesManager?->id]);
        Employee::whereIn('email', ['oscar.pratama@company.com', 'tommy.kurniawan@company.com'])->update(['manager_id' => $opsManager?->id]);
        Employee::where('email', 'nadia.kusuma@company.com')->update(['manager_id' => $csManager?->id]);
        Employee::whereIn('email', ['siti.rahayu@company.com', 'budi.santoso@company.com', 'dewi.lestari@company.com', 'eko.prasetyo@company.com', 'hana.permata@company.com', 'joko.widodo@company.com', 'maya.putri@company.com'])
            ->update(['manager_id' => $ceo?->id]);

        // Create Leave Types (Indonesian Labor Law - UU Ketenagakerjaan)
        $leaveTypes = [
            [
                'name' => 'Cuti Tahunan',
                'code' => 'CT',
                'description' => 'Cuti tahunan sesuai UU No. 13 Tahun 2003 Pasal 79 ayat 2. Diberikan setelah 12 bulan bekerja terus menerus.',
                'days_per_year' => 12,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Cuti Sakit',
                'code' => 'CS',
                'description' => 'Cuti karena sakit dengan surat keterangan dokter. Upah tetap dibayar sesuai ketentuan UU.',
                'days_per_year' => 365,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Cuti Melahirkan',
                'code' => 'CM',
                'description' => 'Cuti melahirkan 3 bulan (1.5 bulan sebelum dan 1.5 bulan sesudah melahirkan) sesuai UU Ketenagakerjaan.',
                'days_per_year' => 90,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Cuti Keguguran',
                'code' => 'CK',
                'description' => 'Cuti 1.5 bulan atau sesuai surat keterangan dokter kandungan.',
                'days_per_year' => 45,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Cuti Menikah',
                'code' => 'CNK',
                'description' => 'Cuti menikah untuk pekerja yang melangsungkan pernikahan.',
                'days_per_year' => 3,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Cuti Menikahkan Anak',
                'code' => 'CMA',
                'description' => 'Cuti untuk menikahkan anak.',
                'days_per_year' => 2,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Cuti Khitanan Anak',
                'code' => 'CKA',
                'description' => 'Cuti untuk mengkhitankan anak.',
                'days_per_year' => 2,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Cuti Baptis Anak',
                'code' => 'CBA',
                'description' => 'Cuti untuk membaptiskan anak.',
                'days_per_year' => 2,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Cuti Istri Melahirkan/Keguguran',
                'code' => 'CIM',
                'description' => 'Cuti untuk suami yang istrinya melahirkan atau mengalami keguguran.',
                'days_per_year' => 2,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Cuti Keluarga Meninggal (Serumah)',
                'code' => 'CKM1',
                'description' => 'Cuti karena anggota keluarga dalam satu rumah meninggal dunia.',
                'days_per_year' => 2,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Cuti Keluarga Meninggal (Orang Tua/Mertua/Anak/Menantu)',
                'code' => 'CKM2',
                'description' => 'Cuti karena orang tua, mertua, anak, atau menantu meninggal dunia.',
                'days_per_year' => 2,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Cuti Haid',
                'code' => 'CH',
                'description' => 'Cuti bagi pekerja wanita yang merasakan sakit pada hari pertama dan kedua masa haid.',
                'days_per_year' => 24,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Cuti Tanpa Gaji',
                'code' => 'CTG',
                'description' => 'Cuti tanpa dibayar untuk keperluan pribadi yang disetujui perusahaan.',
                'days_per_year' => 30,
                'is_paid' => false,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Cuti Besar',
                'code' => 'CB',
                'description' => 'Cuti panjang setelah bekerja 6 tahun terus menerus (jika diatur dalam perjanjian kerja).',
                'days_per_year' => 30,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Izin Keperluan Penting',
                'code' => 'IKP',
                'description' => 'Izin untuk keperluan penting yang tidak dapat ditunda.',
                'days_per_year' => 5,
                'is_paid' => false,
                'requires_approval' => true,
                'is_active' => true,
            ],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::firstOrCreate(
                ['code' => $leaveType['code']],
                $leaveType
            );
        }

        // Create Salary Components
        $salaryComponents = [
            // Earnings
            ['name' => 'Gaji Pokok', 'code' => 'GP', 'type' => 'earning', 'is_taxable' => true, 'is_active' => true, 'default_amount' => 0, 'sort_order' => 1],
            ['name' => 'Tunjangan Jabatan', 'code' => 'TJ', 'type' => 'earning', 'is_taxable' => true, 'is_active' => true, 'default_amount' => 0, 'sort_order' => 2],
            ['name' => 'Tunjangan Transportasi', 'code' => 'TT', 'type' => 'earning', 'is_taxable' => true, 'is_active' => true, 'default_amount' => 500000, 'sort_order' => 3],
            ['name' => 'Tunjangan Makan', 'code' => 'TM', 'type' => 'earning', 'is_taxable' => true, 'is_active' => true, 'default_amount' => 750000, 'sort_order' => 4],
            ['name' => 'Tunjangan Komunikasi', 'code' => 'TK', 'type' => 'earning', 'is_taxable' => true, 'is_active' => true, 'default_amount' => 200000, 'sort_order' => 5],
            ['name' => 'Tunjangan Kesehatan', 'code' => 'TKS', 'type' => 'earning', 'is_taxable' => false, 'is_active' => true, 'default_amount' => 0, 'sort_order' => 6],
            ['name' => 'Tunjangan Keluarga', 'code' => 'TKL', 'type' => 'earning', 'is_taxable' => true, 'is_active' => true, 'default_amount' => 0, 'sort_order' => 7],
            ['name' => 'Uang Lembur', 'code' => 'UL', 'type' => 'earning', 'is_taxable' => true, 'is_active' => true, 'default_amount' => 0, 'sort_order' => 8],
            ['name' => 'Bonus', 'code' => 'BNS', 'type' => 'earning', 'is_taxable' => true, 'is_active' => true, 'default_amount' => 0, 'sort_order' => 9],
            ['name' => 'THR', 'code' => 'THR', 'type' => 'earning', 'is_taxable' => true, 'is_active' => true, 'default_amount' => 0, 'sort_order' => 10],
            ['name' => 'Insentif', 'code' => 'INS', 'type' => 'earning', 'is_taxable' => true, 'is_active' => true, 'default_amount' => 0, 'sort_order' => 11],
            ['name' => 'Komisi', 'code' => 'KMS', 'type' => 'earning', 'is_taxable' => true, 'is_active' => true, 'default_amount' => 0, 'sort_order' => 12],
            // Deductions
            ['name' => 'BPJS Kesehatan (Karyawan)', 'code' => 'BPJSK', 'type' => 'deduction', 'is_taxable' => false, 'is_active' => true, 'default_amount' => 0, 'sort_order' => 20],
            ['name' => 'BPJS Ketenagakerjaan JHT (Karyawan)', 'code' => 'BPJSJHT', 'type' => 'deduction', 'is_taxable' => false, 'is_active' => true, 'default_amount' => 0, 'sort_order' => 21],
            ['name' => 'BPJS Ketenagakerjaan JP (Karyawan)', 'code' => 'BPJSJP', 'type' => 'deduction', 'is_taxable' => false, 'is_active' => true, 'default_amount' => 0, 'sort_order' => 22],
            ['name' => 'PPh 21', 'code' => 'PPH21', 'type' => 'deduction', 'is_taxable' => false, 'is_active' => true, 'default_amount' => 0, 'sort_order' => 23],
            ['name' => 'Potongan Keterlambatan', 'code' => 'PKT', 'type' => 'deduction', 'is_taxable' => false, 'is_active' => true, 'default_amount' => 0, 'sort_order' => 24],
            ['name' => 'Potongan Absensi', 'code' => 'PAB', 'type' => 'deduction', 'is_taxable' => false, 'is_active' => true, 'default_amount' => 0, 'sort_order' => 25],
            ['name' => 'Potongan Pinjaman', 'code' => 'PPJ', 'type' => 'deduction', 'is_taxable' => false, 'is_active' => true, 'default_amount' => 0, 'sort_order' => 26],
            ['name' => 'Potongan Lainnya', 'code' => 'PLN', 'type' => 'deduction', 'is_taxable' => false, 'is_active' => true, 'default_amount' => 0, 'sort_order' => 27],
        ];

        foreach ($salaryComponents as $component) {
            SalaryComponent::firstOrCreate(
                ['code' => $component['code']],
                $component
            );
        }
    }
}
