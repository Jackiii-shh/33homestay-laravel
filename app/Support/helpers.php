<?php

if (! function_exists('rupiah')) {
    /** Format angka menjadi Rupiah, contoh: Rp 350.000 */
    function rupiah(int|float|string|null $amount): string
    {
        return 'Rp '.number_format((float) ($amount ?? 0), 0, ',', '.');
    }
}

if (! function_exists('normalize_phone')) {
    /** Ubah nomor HP lokal (08xx / +62xx) menjadi format internasional tanpa "+" (62xx). */
    function normalize_phone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '' || $digits === null) {
            return null;
        }
        if (str_starts_with($digits, '62')) {
            return $digits;
        }
        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }
        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }

        return $digits;
    }
}

if (! function_exists('wa_link')) {
    /** Tautan wa.me dengan teks terisi otomatis. */
    function wa_link(?string $phone, string $text = ''): string
    {
        $number = normalize_phone($phone) ?? '';

        return 'https://wa.me/'.$number.($text !== '' ? '?text='.rawurlencode($text) : '');
    }
}
