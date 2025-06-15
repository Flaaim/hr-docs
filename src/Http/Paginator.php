<?php

namespace App\Http;

class Paginator {
    private int $currentPage;
    private int $totalItems;
    private int $itemsPerPage;
    private int $maxPagesToShow = 5;

    public function __construct(int $currentPage, int $totalItems, int $itemsPerPage = 10)
    {
        $this->itemsPerPage = max(1, $itemsPerPage);
        $this->totalItems = max(0, $totalItems);
        $this->currentPage = $this->validatePage($currentPage);
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getTotalPages(): int
    {
        return (int) ceil($this->totalItems / $this->itemsPerPage);
    }

    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    public function getOffset(): int
    {
        return ($this->currentPage - 1) * $this->itemsPerPage;
    }

    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->getTotalPages();
    }

    public function getPages(): array
    {
        $pages = [];
        $totalPages = $this->getTotalPages();

        if ($totalPages <= $this->maxPagesToShow) {
            for ($i = 1; $i <= $totalPages; $i++) {
                $pages[] = $i;
            }
        } else {
            $half = floor($this->maxPagesToShow / 2);

            $startPage = max(1, $this->currentPage - $half);
            $endPage = min($totalPages, $startPage + $this->maxPagesToShow - 1);

            if ($endPage - $startPage + 1 < $this->maxPagesToShow) {
                $startPage = max(1, $endPage - $this->maxPagesToShow + 1);
            }

            for ($i = $startPage; $i <= $endPage; $i++) {
                $pages[] = $i;
            }
        }

        return $pages;
    }

    private function validatePage(int $page): int
    {
        $page = max(1, $page);

        if ($this->totalItems > 0 && $page > $this->getTotalPages()) {
            return $this->getTotalPages();
        }

        return $page;
    }
}
