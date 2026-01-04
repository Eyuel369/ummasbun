<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class OwnerRangeExport implements WithMultipleSheets
{
    public function __construct(
        private Carbon $startDate,
        private Carbon $endDate
    ) {
    }

    public function sheets(): array
    {
        return [
            ExportData::metadataSheet('Date Range Export', $this->startDate, $this->endDate),
            ExportData::productsSheet(),
            ExportData::dailySalesSheet($this->startDate, $this->endDate),
            ExportData::saleLinesSheet($this->startDate, $this->endDate),
            ExportData::paymentsSheet($this->startDate, $this->endDate),
            ExportData::expensesSheet($this->startDate, $this->endDate),
            ExportData::inventoryItemsSheet(),
            ExportData::inventoryDailySheet($this->startDate, $this->endDate),
        ];
    }
}
