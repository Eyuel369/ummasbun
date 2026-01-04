<?php

namespace App\Http\Controllers;

use App\Models\InventoryDaily;
use Illuminate\Http\RedirectResponse;

class InventoryController extends Controller
{
    public function today(): RedirectResponse
    {
        $this->authorize('create', InventoryDaily::class);

        return redirect()->route('inventory.daily.index', [
            'date' => now()->toDateString(),
        ]);
    }
}
