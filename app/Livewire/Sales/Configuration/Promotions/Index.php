<?php

namespace App\Livewire\Sales\Configuration\Promotions;

use App\Exports\PromotionsExport;
use App\Imports\PromotionsImport;
use App\Livewire\Concerns\WithIndexComponent;
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
    use WithIndexComponent, WithFileUploads;

    #[Url]
    public string $type = '';

    public bool $showImportModal = false;
    public $importFile = null;
    public array $importResult = [];

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'sort', 'groupBy', 'type']);
        $this->resetPage();
        $this->clearSelection();
    }

    protected function getCustomActiveFilterCount(): int
    {
        return $this->type !== '' ? 1 : 0;
    }

    public function activateSelected(): void
    {
        $count = Promotion::whereIn('id', $this->selected)->update(['is_active' => true]);
        $this->clearSelection();
        session()->flash('success', "{$count} promotions activated.");
    }

    public function deactivateSelected(): void
    {
        $count = Promotion::whereIn('id', $this->selected)->update(['is_active' => false]);
        $this->clearSelection();
        session()->flash('success', "{$count} promotions deactivated.");
    }

    public function deleteSelected(): void
    {
        $count = Promotion::whereIn('id', $this->selected)->delete();
        $this->clearSelection();
        session()->flash('success', "{$count} promotions deleted.");
    }

    public function duplicateSelected(): void
    {
        $promotions = Promotion::with(['rules', 'reward'])->whereIn('id', $this->selected)->get();

        foreach ($promotions as $promotion) {
            $newPromotion = $promotion->replicate();
            $newPromotion->name = $promotion->name.' (Copy)';
            $newPromotion->code = $promotion->code ? $promotion->code.'_COPY' : null;
            $newPromotion->usage_count = 0;
            $newPromotion->is_active = false;
            $newPromotion->save();

            foreach ($promotion->rules as $rule) {
                $newRule = $rule->replicate();
                $newRule->promotion_id = $newPromotion->id;
                $newRule->save();
            }

            if ($promotion->reward) {
                $newReward = $promotion->reward->replicate();
                $newReward->promotion_id = $newPromotion->id;
                $newReward->save();
            }
        }

        $count = count($this->selected);
        $this->clearSelection();
        session()->flash('success', "{$count} promotion(s) duplicated.");
    }

    public function exportSelected()
    {
        $ids = ! empty($this->selected) ? $this->selected : null;
        $filename = 'promotions_'.now()->format('Y-m-d_His').'.xlsx';

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
            $import = new PromotionsImport;
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
            'Apply To', 'Description',
        ];

        $example = [
            'Summer Sale', 'SUMMER20', 'product_discount', '10', 'No', 'Yes',
            '2026-06-01', '2026-08-31', '1000', '3',
            '100000', '', 'Active', 'discount_percent',
            '20', '50000', '', '',
            'order', 'Summer discount promotion',
        ];

        $callback = function () use ($headers, $example) {
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

    protected function getQuery()
    {
        return Promotion::query()
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%")))
            ->when($this->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($this->type, fn ($q) => $q->where('type', $this->type));
    }

    protected function getModelClass(): string
    {
        return Promotion::class;
    }

    public function render()
    {
        $promotions = $this->getQuery()
            ->withCount('usages')
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'page', $this->page);

        return view('livewire.sales.configuration.promotions.index', [
            'promotions' => $promotions,
            'types' => Promotion::TYPES,
        ]);
    }
}
