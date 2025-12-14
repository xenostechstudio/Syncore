<?php

namespace App\Livewire\Concerns;

use Livewire\Attributes\Url;

trait WithManualPagination
{
    #[Url]
    public int $page = 1;

    public int $totalPages = 1;

    public function resetPage(): void
    {
        $this->page = 1;
    }

    public function previousPage(): void
    {
        $this->goToPreviousPage();
    }

    public function nextPage(): void
    {
        if ($this->page < $this->totalPages) {
            $this->goToNextPage();
        }
    }

    public function goToPreviousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function goToNextPage(): void
    {
        $this->page++;
    }

    public function getPage(): int
    {
        return $this->page;
    }
}
