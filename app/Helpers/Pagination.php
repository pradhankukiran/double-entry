<?php

declare(strict_types=1);

namespace DoubleE\Helpers;

class Pagination
{
    public static function paginate(int $totalItems, int $currentPage, int $perPage = 25, string $baseUrl = ''): array
    {
        $totalPages = max(1, (int) ceil($totalItems / $perPage));
        $currentPage = max(1, min($currentPage, $totalPages));
        $offset = ($currentPage - 1) * $perPage;

        return [
            'total_items' => $totalItems,
            'total_pages' => $totalPages,
            'current_page' => $currentPage,
            'per_page' => $perPage,
            'offset' => $offset,
            'has_previous' => $currentPage > 1,
            'has_next' => $currentPage < $totalPages,
            'previous_page' => $currentPage - 1,
            'next_page' => $currentPage + 1,
            'base_url' => $baseUrl,
        ];
    }
}
