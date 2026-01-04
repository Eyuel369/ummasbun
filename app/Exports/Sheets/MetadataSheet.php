<?php

namespace App\Exports\Sheets;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class MetadataSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(
        private string $scope,
        private ?Carbon $startDate,
        private ?Carbon $endDate
    ) {
    }

    public function collection(): Collection
    {
        return collect([
            ['Generated On', now()->format('Y-m-d H:i')],
            ['Scope', $this->scope],
            ['Filter Start', $this->startDate?->toDateString() ?? 'All'],
            ['Filter End', $this->endDate?->toDateString() ?? 'All'],
        ]);
    }

    public function headings(): array
    {
        return ['Key', 'Value'];
    }

    public function title(): string
    {
        return 'Metadata';
    }
}
