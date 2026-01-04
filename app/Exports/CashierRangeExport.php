<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CashierRangeExport implements WithMultipleSheets
{
    public function __construct(
        private Carbon $startDate,
        private Carbon $endDate
    ) {
    }

    public function sheets(): array
    {
        return [
            ExportData::metadataSheet('Cashier Date Range Export', $this->startDate, $this->endDate),
            ExportData::dailySalesSheet($this->startDate, $this->endDate),
            ExportData::saleLinesSheet($this->startDate, $this->endDate),
            ExportData::expensesSheet($this->startDate, $this->endDate),
            ExportData::creditPaymentsSheet($this->startDate, $this->endDate),
        ];
    }
}
