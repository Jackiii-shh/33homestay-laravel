<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\AvailabilityService;
use App\Services\InvoicePdf;
use App\Services\WhatsAppLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PublicController extends Controller
{
    public function home(Request $request, AvailabilityService $availability)
    {
        $types = RoomType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->each(fn (RoomType $t) => $t->setAttribute(
                'units_count',
                Room::bookable()->where('room_type_id', $t->id)->count()
            ));

        $results = null;
        $alternatives = [];
        $searchErrors = null;
        $searched = $request->filled('check_in') || $request->filled('check_out');

        if ($searched) {
            $validator = Validator::make($request->query(), [
                'check_in' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
                'check_out' => ['required', 'date_format:Y-m-d', 'after:check_in'],
                'guests' => ['nullable', 'integer', 'min:1', 'max:10'],
                'room_type' => ['nullable', 'integer', 'exists:room_types,id'],
            ]);

            $validator->after(function ($v) use ($request) {
                if (! $v->errors()->any()
                    && AvailabilityService::nights($request->query('check_in'), $request->query('check_out')) > config('homestay.max_stay_nights')) {
                    $v->errors()->add('check_out', 'Maksimal '.config('homestay.max_stay_nights').' malam per pemesanan.');
                }
            });

            if ($validator->fails()) {
                $searchErrors = $validator->errors();
            } else {
                $in = $request->query('check_in');
                $out = $request->query('check_out');
                $guests = $request->filled('guests') ? (int) $request->query('guests') : null;
                $typeId = $request->filled('room_type') ? (int) $request->query('room_type') : null;

                $results = $availability->summaryByType($in, $out, $guests, $typeId)->map(function (array $row) use ($in, $out, $guests) {
                    $row['wa_url'] = WhatsAppLink::inquiry([
                        'check_in' => $in, 'check_out' => $out, 'guests' => $guests, 'room_type' => $row['type']->name,
                    ]);

                    return $row;
                });

                if ($results->sum('available_units') === 0) {
                    $alternatives = $availability->nearbyWindows($typeId, $in, $out);
                }
            }
        }

        return view('public.landing', [
            'types' => $types,
            'results' => $results,
            'alternatives' => $alternatives,
            'searchErrors' => $searchErrors,
            'searched' => $searched,
            'input' => $request->query(),
        ]);
    }

    /** Halaman publik untuk memeriksa keaslian invoice. */
    public function verifyInvoice(string $number, string $token)
    {
        $invoice = Invoice::with(['reservation.room.type', 'reservation.payments'])
            ->where('number', $number)
            ->where('token', $token)
            ->firstOrFail();

        $masked = Str::of($invoice->reservation->guest_name)->before(' ')->toString().' ***';

        return view('public.invoice-verify', compact('invoice', 'masked'));
    }

    /** Unduh PDF lewat tautan bertanda tangan (dikirim ke tamu via WhatsApp). */
    public function publicInvoicePdf(string $token)
    {
        $invoice = Invoice::where('token', $token)->firstOrFail();

        return InvoicePdf::make($invoice)->download($invoice->number.'.pdf');
    }
}
