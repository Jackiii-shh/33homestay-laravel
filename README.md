# 33HomeStay — Sistem Informasi Ketersediaan & Reservasi Terpusat (Laravel)

Web app internal untuk mencegah **double booking** di 33HomeStay, dibuat berdasarkan hasil interview UMKM,
Template Use Case, dan activity diagram final (aktor: **Customer / Calon Tamu, Sistem, Pengelola, Owner**).

> **Prinsip utama:** customer **tidak bisa memesan sendiri**. Website publik hanya menampilkan **informasi**
> (kamar, harga, ketersediaan) dan **chatbot AI**. Saat customer siap memesan, ia diarahkan ke
> **WhatsApp Business Pengelola**. Seluruh pencatatan reservasi dilakukan Pengelola di dashboard.

## Fitur per aktor

| Aktor | Fitur |
|---|---|
| **Customer / Calon Tamu** (publik, tanpa login) | Landing page lengkap · cek ketersediaan per tanggal/tipe/jumlah tamu (informasi saja) · chatbot AI (info kamar, harga, ketersediaan, arahan ke WhatsApp) · tombol "Pesan via WhatsApp" dengan pesan terisi otomatis · verifikasi keaslian invoice via tautan |
| **Sistem** | Validasi bentrok tanggal otomatis (transaksi + lock baris kamar) · blokir tanggal saat reservasi disimpan · batal otomatis bila DP terlambat · penerbitan invoice PDF bernomor unik · status kamar (Tersedia/Dibooking/Terisi/Dibersihkan/Maintenance) · audit trail |
| **Pengelola** (Admin Sosmed + Admin Lapangan) | Dashboard operasional · reservasi baru (WhatsApp/OTA/walk-in) dengan pemilih kamar yang hanya menampilkan unit kosong · ubah tanggal/perpanjang/batal (selalu diperiksa ulang) · catat DP & pelunasan + invoice PDF + kirim via WhatsApp · check-in (wajib lunas) / check-out / no-show · checklist blokir-buka tanggal OTA manual · kalender ketersediaan · status kamar |
| **Owner** | Dashboard pemantau real-time (pendapatan, okupansi, double booking yang dicegah, piutang, grafik) · kalender & reservasi (read-only) · laporan pembayaran + ekspor CSV · log aktivitas (audit) · kelola tipe/harga kamar, unit kamar, pengguna |

## Pemetaan ke activity diagram

| Langkah diagram | Implementasi |
|---|---|
| Customer menemukan info → memilih tipe kamar & tanggal | Landing page `/` + formulir **Cek Ketersediaan** (informasi) |
| Sistem mengarahkan customer ke WhatsApp Business | Tombol/tautan `wa.me` terisi otomatis (landing & chatbot) |
| Pengelola cek ketersediaan → "kamar available?" | Pemilih kamar di **Reservasi Baru** (`/reservasi/buat`) hanya menampilkan unit kosong |
| Tidak tersedia → informasikan alternatif | Saat ditolak, sistem menampilkan unit lain, tipe lain, dan tanggal terdekat |
| Minta data & DP → cek bukti → valid? | Reservasi tersimpan **Menunggu DP** (tanggal terblokir) → catat pembayaran di halaman detail; tidak valid/terlambat → batal otomatis |
| Validasi bentrok & simpan reservasi | `ReservationService::create()` — transaksi + `lockForUpdate` + pengecekan rentang `[check_in, check_out)` |
| Terbitkan invoice PDF → kirim via WhatsApp | `PaymentService` + tombol **Kirim WA** (tautan PDF bertanda tangan 30 hari + tautan verifikasi) |
| Hari-H: tamu tiba? Ya/Tidak | Dashboard Pengelola: **Check-in** atau **No-show** (setelah batas jam check-in) |
| Lunas? Belum → pelunasan | Check-in diblokir sampai lunas; pelunasan dicatat & menerbitkan struk |
| Status kamar → Booked/Occupied | Booked saat reservasi tersimpan, Terisi saat check-in, Dibersihkan saat check-out |
| Owner memantau laporan | Dashboard Owner, laporan pembayaran, audit |

## Persyaratan
PHP **8.2+** (ekstensi: mbstring, xml, curl, sqlite3 atau pdo_mysql, gd/dom untuk PDF), Composer 2.
Frontend memakai **Tailwind, Alpine.js, Chart.js lewat CDN** sehingga **tidak perlu npm / build**.

## Instalasi
```bash
composer install
cp .env.example .env            # Windows: copy .env.example .env
php artisan key:generate
touch database/database.sqlite  # Windows: type nul > database\database.sqlite
php artisan migrate --seed      # seed = akun + kamar + data contoh
php artisan serve               # buka http://localhost:8000
```
Agar reservasi "Menunggu DP" yang terlambat batal otomatis, jalankan scheduler:
```bash
php artisan schedule:work       # lokal
# server: tambahkan cron  * * * * * cd /path/app && php artisan schedule:run >> /dev/null 2>&1
```
Tes: `php artisan test`

### Akun contoh (GANTI kata sandi setelah login pertama)
| Peran | Email | Kata sandi |
|---|---|---|
| Owner | `owner@33homestay.test` | `password` |
| Pengelola | `pengelola@33homestay.test` | `password` |

Login staf: `/login` (tautan "Login Staf" di footer website).

### Memakai MySQL
Ubah di `.env`: `DB_CONNECTION=mysql`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, lalu `php artisan migrate --seed`.
(Pengunci baris `lockForUpdate` bekerja penuh di MySQL/InnoDB; SQLite cocok untuk demo/pengembangan.)

## Konfigurasi penting (`.env`, `config/homestay.php`)
- `HOMESTAY_WHATSAPP` — nomor WhatsApp Business pengelola (format `62812...` tanpa `+`). **Wajib diisi nomor asli.**
- `HOMESTAY_ADDRESS`, `HOMESTAY_MAPS_URL`, `HOMESTAY_INSTAGRAM`, `HOMESTAY_TIKTOK`.
- **Aturan usulan tim — konfirmasi ke Pak Taufiq:** `HOMESTAY_DP_PERCENTAGE` (50), `HOMESTAY_DP_DEADLINE_HOURS` (6), `HOMESTAY_CHECKIN_CUTOFF` (22:00), `HOMESTAY_DP_FORFEIT_ON_NO_SHOW` (true).
- Unit kamar sesuai info Owner (**6 unit**: Standard AC 3, Standard Kipas 1, Mezanin 1, Tera House 1). **Harga semua tipe serta kapasitas Tera House masih contoh/placeholder** — ubah lewat dashboard Owner → *Tipe & Harga Kamar* / *Unit Kamar* (tipe baru bisa ditambah dari menu yang sama).

### Chatbot AI (opsional)
Isi `ANTHROPIC_API_KEY` (dan `ANTHROPIC_MODEL`, default `claude-haiku-5-5`) di `.env`.
Chatbot memakai *tool use*: ketersediaan **dibaca dari database** (bukan dikarang model) dan tombol WhatsApp dibuat oleh server.
Tanpa API key, chatbot otomatis berjalan di **mode dasar berbasis aturan** (harga, kamar, jam, pembayaran, lokasi, cek tanggal `2026-12-24 sampai 2026-12-26`, arahan ke WhatsApp).
Batas wajar: 20 pesan/menit per IP, riwayat 10 pesan, panjang pesan 600 karakter.

## Aturan bisnis inti
1. **Anti double booking** — dua reservasi aktif (Menunggu DP / Terkonfirmasi / Check-in) pada unit sama bentrok bila `A.check_in < B.check_out` **dan** `A.check_out > B.check_in`. Check-out & check-in di hari yang sama **tidak** bentrok.
2. Validasi dijalankan saat **simpan, ubah tanggal, pindah kamar, dan perpanjang** — di dalam transaksi dengan kunci baris kamar. Percobaan yang ditolak tercatat (*"Double booking dicegah"*) dan dihitung di dashboard Owner.
3. Reservasi baru → **Menunggu DP**, tanggal langsung terblokir. DP ≥ persentase minimum → **Terkonfirmasi**. Lewat batas waktu tanpa DP → **batal otomatis**.
4. Pesanan **OTA** (Agoda/Tiket.com) langsung Terkonfirmasi & tercatat dibayar via OTA. Pesanan WhatsApp/walk-in perlu **diblokir manual di OTA** (checklist di dashboard) — integrasi API OTA di luar scope.
5. **Check-in wajib lunas**; **check-out wajib tanpa sisa tagihan**. Setiap pembayaran menerbitkan invoice PDF bernomor unik + halaman verifikasi publik (`/verifikasi-invoice/{nomor}/{token}`).
6. Semua perubahan dicatat di **log aktivitas** (siapa, kapan, apa) untuk menekan risiko kecurangan.

## Struktur penting
```
app/Services/AvailabilityService.php   # sumber kebenaran ketersediaan & aturan bentrok
app/Services/ReservationService.php    # buat/ubah/batal/check-in/out/no-show (transaksi + lock)
app/Services/PaymentService.php        # pembayaran + invoice
app/Services/ChatbotService.php        # chatbot (tool use) + mode dasar
app/Console/Commands/ExpirePendingReservations.php
routes/web.php   resources/views/{public,staff,owner,pdf}   database/{migrations,seeders}
tests/Feature    # bentrok tanggal, alur pembayaran, hak akses
```

## Di luar scope MVP (sesuai Template Use Case)
Integrasi API OTA (Agoda/Tiket.com/Traveloka) · payment gateway · pembukuan akuntansi lengkap & laba/rugi · absensi/penggajian · inventaris.
Ide lanjutan: sinkronisasi iCal OTA, notifikasi WhatsApp API otomatis, harga musiman/akhir pekan, migrasi data historis dari kertas.

## Catatan keamanan
Ganti kata sandi akun contoh · set `APP_DEBUG=false` di produksi · gunakan HTTPS · isi `APP_URL` dengan domain asli (dipakai untuk tautan invoice bertanda tangan) · batasi akses server `.env`.
Data tamu yang disimpan minimal (nama, kota, no. HP) — **jangan** menyimpan KTP atau data identitas sensitif.
