# Kamus Data (Data Dictionary) - 33HomeStay Reservation Data

* **Nama Proyek:** Centralized Reservation System 33HomeStay Anyer
* **ID Proyek:** A26-CS026
* **Penanggung Jawab:** Dzakwan Muzakki (Data Science - DS014B6Y0494)
* **Kategori:** Wajib (Deliverable Data Science)
* **Status:** Terverifikasi & Bersih (100% Siap Integrasi)

---

## 1. Deskripsi Umum
Kamus data ini mendeskripsikan spesifikasi skema data tabel reservasi yang telah melalui proses *cleaning*, standardisasi format, serta validasi konsistensi dari rekap pencatatan manual UMKM 33HomeStay.

---

## 2. Struktur Kolom Dataset (`clean_reservations.csv`)

| Nama Kolom | Tipe Data | Format / Contoh | Wajib Diisi | Batasan / Validasi | Deskripsi Bisnis |
|:---|:---|:---|:---:|:---|:---|
| `reservation_code` | String (Varchar) | `33H-2026-001` | Ya | Unique, format prefix `33H-` | Kode identifikasi unik transaksi reservasi untuk pelacakan invoice dan staf. |
| `guest_name` | String (Varchar) | `Budi Santoso` | Ya | Min 3 karakter, Title Case | Nama lengkap pelanggan / pemesan kamar. |
| `guest_city` | String (Varchar) | `Jakarta`, `Bekasi` | Ya | Standardisasi nama kota | Kota asal domisili pelanggan untuk analisis segmentasi pasar. |
| `guest_phone` | String (Varchar) | `081234567890` | Ya | Format angka Indonesia (`08...`), 10–13 digit | Nomor WhatsApp aktif tamu untuk konfirmasi dan pengiriman invoice. |
| `room_code` | String (Varchar) | `AC-1`, `TH-1` | Ya | Sesuai kode fisik: `AC-1`, `AC-2`, `AC-3`, `KPS-1`, `MZN-1`, `TH-1` | Kode fisik unit kamar spesifik yang dipesan di penginapan. |
| `room_type` | String (Varchar) | `Standard Room AC` | Ya | 4 varian resmi | Kategori tipe kamar penginapan: Standard AC, Standard Kipas, Mezanin, Tera House. |
| `price_per_night` | Integer | `350000` | Ya | `> 0`, kelipatan Rp 50.000 | Tarif resmi sewa kamar per malam (IDR). |
| `room_capacity` | Integer | `2`, `4` | Ya | Min 1, Max 4 orang | Kapasitas maksimal tamu per unit kamar. |
| `check_in` | Date | `2026-08-15` | Ya | Format ISO-8601 (`YYYY-MM-DD`) | Tanggal mulai menginap tamu (waktu check-in default pkl 14:00). |
| `check_out` | Date | `2026-08-17` | Ya | Format ISO-8601 (`YYYY-MM-DD`), `> check_in` | Tanggal selesai menginap tamu (waktu check-out default pkl 12:00). |
| `nights` | Integer | `2` | Ya | `check_out - check_in`, Min 1 | Durasi total masa menginap dalam hitungan malam. |
| `guest_count` | Integer | `2` | Ya | `1 <= guest_count <= room_capacity` | Jumlah orang tamu yang akan menginap di kamar tersebut. |
| `total_price` | Integer | `700000` | Ya | `price_per_night * nights` | Total kewajiban pembayaran sewa kamar oleh tamu. |
| `status` | Enum (String) | `PAID_OFF`, `DP_PAID` | Ya | `PAID_OFF`, `DP_PAID`, `CONFIRMED`, `CANCELLED` | Status reservasi saat ini di dalam sistem operasional. |
| `channel` | Enum (String) | `Whatsapp`, `Agoda` | Ya | `Whatsapp`, `Agoda`, `Tiket.Com`, `Walk-In` | Kanal masuk pemesanan untuk evaluasi performa pemasaran. |
| `payment_method` | String (Varchar) | `Transfer BCA`, `Cash / Tunai` | Ya | `Transfer BCA`, `Cash / Tunai`, `QRIS`, `OTA Virtual Card` | Saluran keuangan yang digunakan untuk pembayaran DP / pelunasan. |
| `dp_amount` | Integer | `350000` | Ya | Min 50% dari `total_price` | Besaran uang muka (Down Payment) yang sudah disetorkan tamu. |
| `remaining_payment`| Integer | `0`, `350000` | Ya | `total_price - dp_amount` | Sisa tagihan yang harus dibayar saat tamu tiba di homestay. |
| `notes` | String (Text) | `Parkir 1 mobil` | Tidak | Default `-` jika kosong | Catatan khusus permintaan tamu atau kondisi lapangan. |

---

## 3. Matriks Relasi Entitas Data
* **Kamar (`room_code`):**
  * `AC-1`, `AC-2`, `AC-3` &rarr; Relasi ke tipe *Standard Room AC* (Tarif: Rp 350.000, Kapasitas: 2).
  * `KPS-1` &rarr; Relasi ke tipe *Standard Room Kipas* (Tarif: Rp 250.000, Kapasitas: 2).
  * `MZN-1` &rarr; Relasi ke tipe *Mezanin* (Tarif: Rp 600.000, Kapasitas: 4).
  * `TH-1` &rarr; Relasi ke tipe *Tera House* (Tarif: Rp 800.000, Kapasitas: 4).
