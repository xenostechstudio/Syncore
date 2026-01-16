<?php

namespace App\Livewire\Sales\Configuration\Promotions;

use App\Exports\PromotionsExport;
use App\Imports\PromotionsImport;
use App\Models\Sales\Promotion;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Promotions')]
class Index extends Component
{
    use WithFileUploads;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $type = '';

    #[Url]
    public string $view = 'list';

    public int $page = 1;
    public array $selected = [];
    public bool $selectAll = false;

    public bool $showImportModal = false;
    public $importFile = null;
    public array $importResult = [];

    public function setView(string $view): void
    {
        if (in_array($view, ['list', 'grid', 'kanban'])) {
            $this->view = $view;
        }
    }

    public function updatedSearch(): void
    {
        $this->page = 1;
    }

    public function updatedStatus(): void
    {
        $this->page = 1;
    }

    public function updatedType(): void
    {
        $this->page = 1;
    }

    public function updatedSelectAll(): void
    {
        if ($this->selectAll) {
            $this->selected = $this->getPromotionsQuery()->pluck('id')->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function toggleSelect(int $id): void
    {
        if (in_array($id, $this->selected)) {
            $this->selected = array_diff($this->selected, [$id]);
        } else {
            $this->selected[] = $id;
        }
    }

    public function deleteSelected(): void
    {
        Promotion::whereIn('id', $this->selected)->delete();
        $this->selected = [];
        $this->selectAll = false;
        session()->flash('success', 'Selected promotions deleted successfully.');
    }

    public function activateSelected(): void
    {
        Promotion::whereIn('id', $this->selected)->update(['is_active' => true]);
        $this->selected = [];
        $this->selectAll = false;
        session()->flash('success', count($this->selected) . ' promotions activated.');
    }

    public function deactivateSelected(): void
    {
        Promotion::whereIn('id', $this->selected)->update(['is_active' => false]);
        $this->selected = [];
        $this->selectAll = false;
        session()->flash('success', count($this->selected) . ' promotions deactivated.');
    }

    public function duplicateSelected(): void
    {
        $promotions = Promotion::with(['rules', 'reward'])->whereIn('id', $this->selected)->get();
        
        foreach ($promotions as $promotion) {
            $newPromotion = $promotion->replicate();
            $newPromotion->name = $promotion->name . ' (Copy)';
            $newPromotion->code = $promotion->code ? $promotion->code . '_COPY' : null;
            $newPromotion->usage_count = 0;
            $newPromotion->is_active = false;
            $newPromotion->save();

            // Duplicate rules
            foreach ($promotion->rules as $rule) {
                $newRule = $rule->replicate();
                $newRule->promotion_id = $newPromotion->id;
                $newRule->save();
            }

            // Duplicate reward
            if ($promotion->reward) {
                $newReward = $promotion->reward->replicate();
                $newReward->promotion_id = $newPromotion->id;
                $newReward->save();
            }
        }

        $count = count($this->selected);
        $this->selected = [];
        $this->selectAll = false;
        session()->flash('success', $count . ' promotion(s) duplicated.');
    }

    public function export()
    {
        $ids = !empty($this->selected) ? $this->selected : null;
        $filename = 'promotions_' . now()->format('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new PromotionsExport($ids), $filename);
    }

    public function openImportModal(): void
    {
        $this->showImportModal = true;
        $this->importFile = null;
        $this->importResult = [];
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->importFile = null;
        $this->importResult = [];
    }

    public function import(): void
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new PromotionsImport();
            Excel::import($import, $this->importFile->getRealPath());

            $this->importResult = [
                'success' => true,
                'imported' => $import->imported,
                'updated' => $import->updated,
                'errors' => $import->errors,
            ];

            if (empty($import->errors)) {
                session()->flash('success', "Import completed: {$import->imported} created, {$import->updated} updated.");
                $this->closeImportModal();
            }
        } catch (\Exception $e) {
            $this->importResult = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Name', 'Code', 'Type', 'Priority', 'Combinable', 'Requires Coupon',
            'Start Date', 'End Date', 'Usage Limit', 'Per Customer',
            'Min Order Amount', 'Min Quantity', 'Status', 'Reward Type',
            'Discount Value', 'Max Discount', 'Buy Quantity', 'Get Quantity',
            'Apply To', 'Description'
        ];

        $example = [
            'Summer Sale', 'SUMMER20', 'product_discount', '10', 'No', 'Yes',
            '2026-06-01', '2026-08-31', '1000', '3',
            '100000', '', 'Active', 'discount_percent',
            '20', '50000', '', '',
            'order', 'Summer discount promotion'
        ];

        $callback = function() use ($headers, $example) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fputcsv($file, $example);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="promotions_template.csv"',
        ]);
    }

    protected function getPromotionsQuery()
    {
        return Promotion::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('code', 'like', "%{$this->search}%");
                });
            })
            ->when($this->status !== '', function ($query) {
                if ($this->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($this->status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->when($this->type, function ($query) {
                $query->where('type', $this->type);
            })
            ->orderByDesc('created_at');
    }

    public function render()
    {
        $promotions = $this->getPromotionsQuery()
            ->withCount('usages')
            ->paginate(15, ['*'], 'page', $this->page);

        return view('livewire.sales.configuration.promotions.index', [
            'promotions' => $promotions,
            'types' => Promotion::TYPES,
        ]);
    }
}
