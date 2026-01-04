<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InventoryRangeExport implements WithMultipleSheets
{
    public function __construct(
        private Carbon $startDate,
        private Carbon $endDate
    ) {
    }

    public function sheets(): array
    {
        return [
            ExportData::metadataSheet('Inventory Date Range Export', $this->startDate, $this->endDate),
            ExportData::inventoryItemsSheet(),
            ExportData::inventoryDailySheet($this->startDate, $this->endDate),
        ];
    }
}
