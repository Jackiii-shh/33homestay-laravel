<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PaymentReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $this->date($request->query('from'), now()->startOfMonth());
        $to = $this->date($request->query('to'), now());

        $base = Payment::query()
            ->whereBetween('paid_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->when($request->filled('method'), fn ($q) => $q->where('method', $request->query('method')))
            ->when($request->filled('user'), fn ($q) => $q->where('received_by', (int) $request->query('user')));

        if ($request->query('export') === 'csv') {
            $rows = (clone $base)->with(['reservation.room', 'invoice', 'receiver'])->orderBy('paid_at')->get();

            return response()->streamDownload(function () use ($rows) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Waktu', 'Invoice', 'Reservasi', 'Tamu', 'Kamar', 'Jenis', 'Metode', 'Nominal', 'Dicatat oleh']);
                foreach ($rows as $p) {
                    fputcsv($out, [
                        $p->paid_at->format('Y-m-d H:i'),
                        $p->invoice?->number,
                        $p->reservation->code,
                        $p->reservation->guest_name,
                        $p->reservation->room->code,
                        $p->typeLabel(),
                        $p->methodLabel(),
                        $p->amount,
                        $p->receiver?->name,
                    ]);
                }
                fclose($out);
            }, 'pembayaran-'.$from->toDateString().'_'.$to->toDateString().'.csv', ['Content-Type' => 'text/csv']);
        }

        $total = (int) (clone $base)->sum('amount');
        $byMethod = (clone $base)->selectRaw('method, COUNT(*) as n, SUM(amount) as total')->groupBy('method')->get();
        $byUser = (clone $base)->selectRaw('received_by, COUNT(*) as n, SUM(amount) as total')->groupBy('received_by')->get()
            ->map(function ($row) {
                $row->user_name = $row->received_by ? (User::find($row->received_by)?->name ?? '-') : 'Sistem/OTA';

                return $row;
            });

        $payments = $base->with(['reservation.room', 'invoice', 'receiver'])->orderByDesc('paid_at')->paginate(20)->withQueryString();

        return view('owner.payments', [
            'payments' => $payments,
            'total' => $total,
            'byMethod' => $byMethod,
            'byUser' => $byUser,
            'from' => $from,
            'to' => $to,
            'staff' => User::orderBy('name')->get(),
        ]);
    }

    private function date(?string $value, Carbon $default): Carbon
    {
        try {
            return $value ? Carbon::createFromFormat('Y-m-d', $value)->startOfDay() : $default->copy()->startOfDay();
        } catch (\Throwable) {
            return $default->copy()->startOfDay();
        }
    }
}
