<?php

namespace App\Services;

use Illuminate\Support\Carbon;

/** Pembuat tautan WhatsApp Business pengelola dengan pesan terisi otomatis. */
class WhatsAppLink
{
    /**
     * @param  array{check_in?:?string, check_out?:?string, room_type?:?string, guests?:?int, name?:?string}  $inquiry
     */
    public static function inquiry(array $inquiry = []): string
    {
        $parts = ['Halo '.config('homestay.name').', saya ingin memesan kamar'];

        if (! empty($inquiry['room_type'])) {
            $parts[0] .= ' '.$inquiry['room_type'];
        }
        if (! empty($inquiry['guests'])) {
            $parts[] = 'untuk '.(int) $inquiry['guests'].' orang';
        }
        if (! empty($inquiry['check_in']) && ! empty($inquiry['check_out'])) {
            $parts[] = 'check-in '.Carbon::parse($inquiry['check_in'])->translatedFormat('d F Y')
                .', check-out '.Carbon::parse($inquiry['check_out'])->translatedFormat('d F Y');
        }

        $text = implode(', ', $parts).'. Mohon info ketersediaan dan prosedur DP. Terima kasih.';
        if (! empty($inquiry['name'])) {
            $text .= ' (a.n. '.$inquiry['name'].')';
        }

        return 'https://wa.me/'.config('homestay.whatsapp').'?text='.rawurlencode($text);
    }

    public static function general(): string
    {
        return self::inquiry();
    }
}
