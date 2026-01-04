<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CreditController extends Controller
{
    public function index(): View
    {
        Gate::authorize('view-debtors');

        $credits = Payment::with('dailySale')
            ->where('method', 'credit')
            ->orderBy('customer_name')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('customer_name');

        $totals = $credits->map(fn ($items) => $items->sum('amount'));

        return view('credit.index', [
            'credits' => $credits,
            'totals' => $totals,
        ]);
    }

    public function export(): Response
    {
        Gate::authorize('view-debtors');

        $user = auth()->user();
        if (! $user || $user->role !== User::ROLE_OWNER) {
            abort(403);
        }

        $credits = Payment::where('method', 'credit')
            ->orderBy('customer_name')
            ->get()
            ->groupBy('customer_name');

        $lines = [];
        $lines[] = ['Customer', 'Total Owed'];
        foreach ($credits as $customer => $items) {
            $lines[] = [$customer ?: 'Unknown', number_format($items->sum('amount'), 2, '.', '')];
        }

        $handle = fopen('php://temp', 'rb+');
        foreach ($lines as $line) {
            fputcsv($handle, $line);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="credit-list.csv"',
        ]);
    }
}
