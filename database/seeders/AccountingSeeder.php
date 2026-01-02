<?php

namespace Database\Seeders;

use App\Models\Accounting\Account;
use App\Models\Accounting\FiscalPeriod;
use Illuminate\Database\Seeder;

class AccountingSeeder extends Seeder
{
    public function run(): void
    {
        // Create Fiscal Period
        FiscalPeriod::create([
            'name' => 'FY ' . now()->year,
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'status' => 'open',
        ]);

        // Chart of Accounts
        $accounts = [
            // Assets (1xxx)
            ['code' => '1000', 'name' => 'Assets', 'type' => 'asset', 'is_system' => true],
            ['code' => '1100', 'name' => 'Cash and Bank', 'type' => 'asset', 'parent' => '1000'],
            ['code' => '1110', 'name' => 'Cash on Hand', 'type' => 'asset', 'parent' => '1100'],
            ['code' => '1120', 'name' => 'Bank - BCA', 'type' => 'asset', 'parent' => '1100'],
            ['code' => '1130', 'name' => 'Bank - Mandiri', 'type' => 'asset', 'parent' => '1100'],
            ['code' => '1200', 'name' => 'Accounts Receivable', 'type' => 'asset', 'parent' => '1000', 'is_system' => true],
            ['code' => '1300', 'name' => 'Inventory', 'type' => 'asset', 'parent' => '1000', 'is_system' => true],
            ['code' => '1400', 'name' => 'Prepaid Expenses', 'type' => 'asset', 'parent' => '1000'],
            ['code' => '1500', 'name' => 'Fixed Assets', 'type' => 'asset', 'parent' => '1000'],
            ['code' => '1510', 'name' => 'Equipment', 'type' => 'asset', 'parent' => '1500'],
            ['code' => '1520', 'name' => 'Vehicles', 'type' => 'asset', 'parent' => '1500'],
            ['code' => '1590', 'name' => 'Accumulated Depreciation', 'type' => 'asset', 'parent' => '1500'],

            // Liabilities (2xxx)
            ['code' => '2000', 'name' => 'Liabilities', 'type' => 'liability', 'is_system' => true],
            ['code' => '2100', 'name' => 'Accounts Payable', 'type' => 'liability', 'parent' => '2000', 'is_system' => true],
            ['code' => '2200', 'name' => 'Accrued Expenses', 'type' => 'liability', 'parent' => '2000'],
            ['code' => '2300', 'name' => 'Tax Payable', 'type' => 'liability', 'parent' => '2000'],
            ['code' => '2310', 'name' => 'VAT Payable', 'type' => 'liability', 'parent' => '2300'],
            ['code' => '2320', 'name' => 'Income Tax Payable', 'type' => 'liability', 'parent' => '2300'],
            ['code' => '2400', 'name' => 'Short-term Loans', 'type' => 'liability', 'parent' => '2000'],
            ['code' => '2500', 'name' => 'Long-term Loans', 'type' => 'liability', 'parent' => '2000'],

            // Equity (3xxx)
            ['code' => '3000', 'name' => 'Equity', 'type' => 'equity', 'is_system' => true],
            ['code' => '3100', 'name' => 'Share Capital', 'type' => 'equity', 'parent' => '3000'],
            ['code' => '3200', 'name' => 'Retained Earnings', 'type' => 'equity', 'parent' => '3000', 'is_system' => true],
            ['code' => '3300', 'name' => 'Current Year Earnings', 'type' => 'equity', 'parent' => '3000', 'is_system' => true],

            // Revenue (4xxx)
            ['code' => '4000', 'name' => 'Revenue', 'type' => 'revenue', 'is_system' => true],
            ['code' => '4100', 'name' => 'Sales Revenue', 'type' => 'revenue', 'parent' => '4000', 'is_system' => true],
            ['code' => '4200', 'name' => 'Service Revenue', 'type' => 'revenue', 'parent' => '4000'],
            ['code' => '4300', 'name' => 'Other Income', 'type' => 'revenue', 'parent' => '4000'],
            ['code' => '4900', 'name' => 'Sales Discounts', 'type' => 'revenue', 'parent' => '4000'],

            // Expenses (5xxx-6xxx)
            ['code' => '5000', 'name' => 'Cost of Goods Sold', 'type' => 'expense', 'is_system' => true],
            ['code' => '5100', 'name' => 'Product Cost', 'type' => 'expense', 'parent' => '5000'],
            ['code' => '5200', 'name' => 'Shipping Cost', 'type' => 'expense', 'parent' => '5000'],
            ['code' => '6000', 'name' => 'Operating Expenses', 'type' => 'expense'],
            ['code' => '6100', 'name' => 'Salaries & Wages', 'type' => 'expense', 'parent' => '6000'],
            ['code' => '6200', 'name' => 'Rent Expense', 'type' => 'expense', 'parent' => '6000'],
            ['code' => '6300', 'name' => 'Utilities', 'type' => 'expense', 'parent' => '6000'],
            ['code' => '6400', 'name' => 'Office Supplies', 'type' => 'expense', 'parent' => '6000'],
            ['code' => '6500', 'name' => 'Marketing & Advertising', 'type' => 'expense', 'parent' => '6000'],
            ['code' => '6600', 'name' => 'Depreciation Expense', 'type' => 'expense', 'parent' => '6000'],
            ['code' => '6700', 'name' => 'Insurance', 'type' => 'expense', 'parent' => '6000'],
            ['code' => '6800', 'name' => 'Professional Fees', 'type' => 'expense', 'parent' => '6000'],
            ['code' => '6900', 'name' => 'Miscellaneous Expense', 'type' => 'expense', 'parent' => '6000'],
        ];

        $createdAccounts = [];

        foreach ($accounts as $data) {
            $parentId = null;
            if (isset($data['parent'])) {
                $parentId = $createdAccounts[$data['parent']]->id ?? null;
                unset($data['parent']);
            }

            $account = Account::create(array_merge($data, [
                'parent_id' => $parentId,
                'is_system' => $data['is_system'] ?? false,
            ]));

            $createdAccounts[$data['code']] = $account;
        }
    }
}
