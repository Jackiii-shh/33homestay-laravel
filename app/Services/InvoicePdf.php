<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdf
{
    public static function make(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $invoice->load(['reservation.room.type', 'reservation.payments', 'payment', 'issuer']);

        return Pdf::loadView('pdf.invoice', ['invoice' => $invoice])
            ->setPaper('a5', 'portrait')
            ->setOptions(['defaultFont' => 'DejaVu Sans']);
    }
}
