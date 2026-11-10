<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Reservation;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function store(Request $request, Reservation $reservation, PaymentService $payments)
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'paid_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:200'],
        ]);

        $payment = $payments->record($reservation, $data, $request->user());

        return redirect()->route('reservations.show', $reservation)
            ->with('success', 'Pembayaran '.$payment->typeLabel().' '.rupiah($payment->amount).' dicatat. Invoice '.$payment->invoice->number.' telah terbit - kirim ke tamu via WhatsApp.');
    }
}
