#!/usr/bin/env php
<?php

/**
 * Script to update all Livewire components from WithPagination to WithManualPagination
 */

$files = [
    'app/Livewire/HR/Leave/Types/Index.php',
    'app/Livewire/HR/Leave/Requests/Index.php',
    'app/Livewire/HR/Payroll/Index.php',
    'app/Livewire/HR/Positions/Index.php',
    'app/Livewire/HR/Departments/Index.php',
    'app/Livewire/Inventory/Categories/Index.php',
    'app/Livewire/Inventory/Warehouses/Index.php',
    'app/Livewire/Inventory/Transfers/Index.php',
    'app/Livewire/Inventory/Adjustments/Index.php',
    'app/Livewire/CRM/Activities/Index.php',
    'app/Livewire/CRM/Leads/Index.php',
    'app/Livewire/CRM/Opportunities/Index.php',
    'app/Livewire/Accounting/Accounts/Index.php',
    'app/Livewire/Accounting/JournalEntries/Index.php',
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "Skipping $file (not found)\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $original = $content;
    
    // Replace import
    $content = str_replace(
        'use Livewire\WithPagination;',
        'use App\Livewire\Concerns\WithManualPagination;',
        $content
    );
    
    // Replace trait usage
    $content = preg_replace(
        '/use WithPagination;/',
        'use WithManualPagination;',
        $content
    );
    
    // Replace resetPage() calls
    $content = str_replace('$this->resetPage()', '$this->page = 1', $content);
    
    // Replace previousPage() and nextPage() methods
    $content = preg_replace(
        '/public function goToPreviousPage\(\): void\s*\{\s*\$this->previousPage\(\);\s*\}/s',
        '',
        $content
    );
    
    $content = preg_replace(
        '/public function goToNextPage\(\): void\s*\{\s*\$this->nextPage\(\);\s*\}/s',
        '',
        $content
    );
    
    // Update paginate() calls to include page parameter
    $content = preg_replace(
        '/->paginate\((\d+)\)/',
        '->paginate($1, [\'*\'], \'page\', $this->page)',
        $content
    );
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "✓ Updated $file\n";
    } else {
        echo "- No changes needed for $file\n";
    }
}

echo "\nDone!\n";
