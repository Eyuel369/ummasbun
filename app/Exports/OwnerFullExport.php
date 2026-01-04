<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class OwnerFullExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            ExportData::metadataSheet('Full Export', null, null),
            ExportData::productsSheet(),
            ExportData::dailySalesSheet(null, null),
            ExportData::saleLinesSheet(null, null),
            ExportData::paymentsSheet(null, null),
            ExportData::expensesSheet(null, null),
            ExportData::inventoryItemsSheet(),
            ExportData::inventoryDailySheet(null, null),
        ];
    }
}
