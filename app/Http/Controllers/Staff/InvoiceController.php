<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoicePdf;

class InvoiceController extends Controller
{
    public function pdf(Invoice $invoice)
    {
        return InvoicePdf::make($invoice)->download($invoice->number.'.pdf');
    }

    /** Buka WhatsApp tamu dengan pesan berisi tautan invoice (dikirim manual dari WhatsApp Business). */
    public function whatsapp(Invoice $invoice)
    {
        $invoice->load('reservation.room.type');
        $r = $invoice->reservation;

        $text = sprintf(
            "Halo Kak %s, terima kasih sudah memesan di %s.\n\n%s %s\nReservasi: %s (%s, %s s.d. %s)\nNominal diterima: %s\nSisa tagihan: %s\n\nUnduh PDF: %s\nVerifikasi keaslian: %s",
            $r->guest_name, config('homestay.name'), $invoice->title(), $invoice->number,
            $r->code, $r->room->type->name,
            $r->check_in->translatedFormat('d M Y'), $r->check_out->translatedFormat('d M Y'),
            rupiah($invoice->amount), rupiah($r->balance),
            $invoice->publicPdfUrl(), $invoice->verifyUrl(),
        );

        if (! $r->guest_phone) {
            return back()->with('error', 'No. HP tamu belum diisi. Ubah reservasi untuk menambahkan nomor.');
        }

        return redirect()->away(wa_link($r->guest_phone, $text));
    }
}
