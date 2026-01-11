<?php

namespace App\Http\Controllers;

use App\Imports\CustomersImport;
use App\Imports\ProductsImport;
use App\Imports\SuppliersImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function products(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new ProductsImport();
        Excel::import($import, $request->file('file'));

        $message = "Import completed: {$import->imported} created, {$import->updated} updated.";
        
        if (!empty($import->errors)) {
            $message .= " Errors: " . count($import->errors);
        }

        return back()->with('success', $message);
    }

    public function customers(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new CustomersImport();
        Excel::import($import, $request->file('file'));

        $message = "Import completed: {$import->imported} created, {$import->updated} updated.";
        
        if (!empty($import->errors)) {
            $message .= " Errors: " . count($import->errors);
        }

        return back()->with('success', $message);
    }

    public function suppliers(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new SuppliersImport();
        Excel::import($import, $request->file('file'));

        $message = "Import completed: {$import->imported} created, {$import->updated} updated.";
        
        if (!empty($import->errors)) {
            $message .= " Errors: " . count($import->errors);
        }

        return back()->with('success', $message);
    }

    public function downloadTemplate(string $type)
    {
        $templates = [
            'products' => [
                'headers' => ['name', 'sku', 'category', 'type', 'cost_price', 'selling_price', 'quantity', 'min_stock', 'description', 'status'],
                'filename' => 'products-template.csv',
            ],
            'customers' => [
                'headers' => ['name', 'email', 'phone', 'company', 'address', 'city', 'state', 'postal_code', 'country', 'tax_id', 'notes'],
                'filename' => 'customers-template.csv',
            ],
            'suppliers' => [
                'headers' => ['name', 'email', 'phone', 'company', 'address', 'city', 'state', 'postal_code', 'country', 'tax_id', 'bank_name', 'bank_account', 'notes'],
                'filename' => 'suppliers-template.csv',
            ],
        ];

        if (!isset($templates[$type])) {
            abort(404);
        }

        $template = $templates[$type];
        $content = implode(',', $template['headers']) . "\n";

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $template['filename'] . '"');
    }
}
