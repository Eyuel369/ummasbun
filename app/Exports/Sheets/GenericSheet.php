<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class GenericSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    /**
     * @param array<int, string> $headings
     * @param Collection<int, array<int, mixed>> $rows
     */
    public function __construct(
        private string $title,
        private array $headings,
        private Collection $rows
    ) {
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return $this->title;
    }
}
