<?php

/*
|--------------------------------------------------------------------------
| Profil & aturan operasional 33HomeStay
|--------------------------------------------------------------------------
| Nilai bertanda [USULAN] belum dikonfirmasi saat interview dengan Owner.
| Konfirmasikan ke Pak Taufiq sebelum dipakai operasional.
*/
return [
    'name' => env('HOMESTAY_NAME', '33HomeStay'),
    'tagline' => 'Penginapan nyaman untuk keluarga, pasangan, dan grup kecil',
    'whatsapp' => preg_replace('/\D+/', '', (string) env('HOMESTAY_WHATSAPP', '6281234567890')),
    'instagram' => env('HOMESTAY_INSTAGRAM', 'villadianyer'),
    'tiktok' => env('HOMESTAY_TIKTOK', 'villadianyer'),
    'address' => env('HOMESTAY_ADDRESS', 'Alamat 33HomeStay (isi alamat lengkap)'),
    'maps_url' => env('HOMESTAY_MAPS_URL', 'https://maps.google.com'),

    'checkin_time' => '14:00',
    'checkout_time' => '12:00',

    // [USULAN] persentase DP minimum dari total tagihan
    'dp_percentage' => (int) env('HOMESTAY_DP_PERCENTAGE', 50),
    // [USULAN] batas waktu DP sejak reservasi disimpan (jam). Lewat batas = batal otomatis
    'dp_deadline_hours' => (int) env('HOMESTAY_DP_DEADLINE_HOURS', 6),
    // [USULAN] batas jam check-in pada hari H; lewat batas Pengelola boleh menandai no-show
    'checkin_cutoff' => env('HOMESTAY_CHECKIN_CUTOFF', '22:00'),
    // [USULAN] DP hangus bila tamu no-show
    'dp_forfeit_on_no_show' => filter_var(env('HOMESTAY_DP_FORFEIT_ON_NO_SHOW', true), FILTER_VALIDATE_BOOLEAN),

    'max_stay_nights' => 14,
    'max_advance_days' => 365,
];
