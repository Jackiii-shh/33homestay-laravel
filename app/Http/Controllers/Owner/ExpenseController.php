<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $month = $request->query('month', now()->format('Y-m'));
        $start = Carbon::parse($month.'-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        // Query pengeluaran bulan ini
        $expenses = Expense::query()
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->get();

        $totalTetap = (int) $expenses->where('type', Expense::TYPE_TETAP)->sum('amount');
        $totalVariabel = (int) $expenses->where('type', Expense::TYPE_VARIABEL)->sum('amount');
        $totalPengeluaran = $totalTetap + $totalVariabel;

        // Omzet uang masuk dari pembayaran yang dibayar pada bulan ini
        $omzetBulan = (int) Payment::query()
            ->whereBetween('paid_at', [$start, $end])
            ->sum('amount');

        // Net Profit (Omzet - Pengeluaran)
        $netProfit = $omzetBulan - $totalPengeluaran;

        // Breakdown biaya listrik per kamar (K1..K5)
        $listrikPerKamar = Expense::query()
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->where('category', 'listrik')
            ->whereNotNull('room_code')
            ->groupBy('room_code')
            ->selectRaw('room_code, sum(amount) as total')
            ->pluck('total', 'room_code')
            ->all();

        return view('owner.expenses.index', compact(
            'expenses', 'month', 'start', 'end',
            'totalTetap', 'totalVariabel', 'totalPengeluaran',
            'omzetBulan', 'netProfit', 'listrikPerKamar'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:tetap,variabel'],
            'category' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:150'],
            'amount' => ['required', 'integer', 'min:1000'],
            'expense_date' => ['required', 'date_format:Y-m-d'],
            'room_code' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $data['created_by'] = $request->user()?->id;
        Expense::create($data);

        return back()->with('success', 'Catatan pengeluaran operasional berhasil disimpan.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return back()->with('success', 'Catatan pengeluaran berhasil dihapus.');
    }
}
