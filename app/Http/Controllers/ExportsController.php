<?php

namespace App\Http\Controllers;

use App\Exports\CashierRangeExport;
use App\Exports\InventoryRangeExport;
use App\Exports\OwnerFullExport;
use App\Exports\OwnerRangeExport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        if (! $user || ! in_array($user->role, [User::ROLE_OWNER, User::ROLE_CASHIER, User::ROLE_STOCK_MANAGER], true)) {
            abort(403);
        }

        [$startDate, $endDate] = $this->resolveRange(
            $request->query('start', now()->subDays(6)->toDateString()),
            $request->query('end', now()->toDateString())
        );

        return view('exports.index', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'role' => $user->role,
        ]);
    }

    public function salesExpenses(): RedirectResponse
    {
        return redirect()->route('exports.index');
    }

    public function inventory(): RedirectResponse
    {
        return redirect()->route('exports.index');
    }

    public function ownerFull(Request $request): BinaryFileResponse
    {
        $user = $request->user();
        if (! $user || $user->role !== User::ROLE_OWNER) {
            abort(403);
        }

        return Excel::download(new OwnerFullExport(), $this->fileName('ummasbun-full'));
    }

    public function ownerRange(Request $request): BinaryFileResponse
    {
        $user = $request->user();
        if (! $user || $user->role !== User::ROLE_OWNER) {
            abort(403);
        }

        [$startDate, $endDate] = $this->resolveRange(
            $request->query('start', ''),
            $request->query('end', '')
        );

        return Excel::download(new OwnerRangeExport($startDate, $endDate), $this->fileName('ummasbun-range'));
    }

    public function cashierRange(Request $request): BinaryFileResponse
    {
        $user = $request->user();
        if (! $user || ! $user->hasRole(User::ROLE_CASHIER)) {
            abort(403);
        }

        [$startDate, $endDate] = $this->resolveRange(
            $request->query('start', ''),
            $request->query('end', '')
        );

        return Excel::download(new CashierRangeExport($startDate, $endDate), $this->fileName('ummasbun-cashier-range'));
    }

    public function inventoryRange(Request $request): BinaryFileResponse
    {
        $user = $request->user();
        if (! $user || ! $user->hasRole(User::ROLE_STOCK_MANAGER)) {
            abort(403);
        }

        [$startDate, $endDate] = $this->resolveRange(
            $request->query('start', ''),
            $request->query('end', '')
        );

        return Excel::download(new InventoryRangeExport($startDate, $endDate), $this->fileName('ummasbun-inventory-range'));
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveRange(string $start, string $end): array
    {
        $startDate = $this->parseDate($start);
        $endDate = $this->parseDate($end);

        if ($startDate->gt($endDate)) {
            return [$endDate, $startDate];
        }

        return [$startDate, $endDate];
    }

    private function parseDate(string $date): Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (\Exception $exception) {
            return now()->startOfDay();
        }
    }

    private function fileName(string $prefix): string
    {
        return $prefix.'-'.now()->format('Ymd_His').'.xlsx';
    }
}
