<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Pencatatan pembayaran + penerbitan invoice/struk digital (PDF).
 * Setiap pembayaran SELALU menghasilkan satu invoice bernomor unik sehingga nominal
 * dapat diverifikasi silang antara tamu, Pengelola, dan catatan internal (anti-fraud).
 */
class PaymentService
{
    /**
     * @param  array{amount:int|string, method:string, paid_at?:mixed, note?:?string}  $data
     */
    public function record(Reservation $reservation, array $data, ?User $user = null): Payment
    {
        return DB::transaction(function () use ($reservation, $data, $user) {
            $r = Reservation::query()->whereKey($reservation->id)->lockForUpdate()->firstOrFail();

            if (! $r->isActiveBooking()) {
                throw ValidationException::withMessages([
                    'amount' => 'Reservasi berstatus "'.$r->statusLabel().'" tidak dapat menerima pembayaran.',
                ]);
            }

            $amount = (int) $data['amount'];
            $paidBefore = (int) $r->payments()->sum('amount');
            $balance = (int) $r->total_price - $paidBefore;

            if ($amount < 1) {
                throw ValidationException::withMessages(['amount' => 'Nominal pembayaran harus lebih dari 0.']);
            }
            if ($amount > $balance) {
                throw ValidationException::withMessages([
                    'amount' => 'Nominal melebihi sisa tagihan ('.rupiah($balance).').',
                ]);
            }
            if ($r->status === Reservation::STATUS_PENDING && ($paidBefore + $amount) < $r->minimumDp()) {
                throw ValidationException::withMessages([
                    'amount' => 'DP minimal '.rupiah($r->minimumDp()).' ('.config('homestay.dp_percentage').'% dari total).',
                ]);
            }

            $type = $paidBefore === 0
                ? ($amount >= $r->total_price ? 'full' : 'dp')
                : 'pelunasan';

            $payment = $r->payments()->create([
                'type' => $type,
                'amount' => $amount,
                'method' => $data['method'],
                'paid_at' => ! empty($data['paid_at']) ? Carbon::parse($data['paid_at']) : now(),
                'note' => $data['note'] ?? null,
                'received_by' => $user?->id,
            ]);

            if ($r->status === Reservation::STATUS_PENDING) {
                $r->update(['status' => Reservation::STATUS_CONFIRMED, 'dp_deadline' => null, 'updated_by' => $user?->id]);
            }

            $invoice = $this->issueInvoice($r, $payment, $user);

            ActivityLog::record(
                'payment_recorded',
                $r,
                sprintf(
                    'Pembayaran %s %s (%s) untuk %s a.n. %s dicatat; invoice %s terbit. Sisa tagihan %s.',
                    $payment->typeLabel(),
                    rupiah($amount),
                    $payment->methodLabel(),
                    $r->code,
                    $r->guest_name,
                    $invoice->number,
                    rupiah($balance - $amount),
                ),
                ['payment_id' => $payment->id, 'invoice' => $invoice->number, 'amount' => $amount],
                $user?->id,
            );

            return $payment->load('invoice');
        });
    }

    private function issueInvoice(Reservation $reservation, Payment $payment, ?User $user): Invoice
    {
        $invoice = Invoice::create([
            'number' => 'TMP-'.Str::uuid(),
            'token' => Str::random(32),
            'reservation_id' => $reservation->id,
            'payment_id' => $payment->id,
            'type' => $payment->type,
            'amount' => $payment->amount,
            'issued_at' => now(),
            'issued_by' => $user?->id,
        ]);

        // Nomor berurutan berbasis id => unik & tidak bisa bentrok walau dibuat bersamaan.
        $invoice->update(['number' => sprintf('INV-%s-%05d', now()->format('Ym'), $invoice->id)]);

        return $invoice;
    }
}
