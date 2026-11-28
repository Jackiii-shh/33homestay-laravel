# Kamus Data (Data Dictionary) - 33HomeStay Reservation Data
## Dataset Sintetis Terkalibrasi Pola Buku Kas Aktual (Januari – November 2026)

* **Nama Proyek:** Centralized Reservation System 33HomeStay Anyer
* **ID Proyek:** A26-CS026
* **Penanggung Jawab:**
  1. **Dewa Bagus Pradnyana Cahyadi Putra (DS014B6Y0434)** — *Lead Data Science Engineer*
  2. **Dzakwan Muzakki (DS014B6Y0494)** — *Data Science Collaborator*
* **Kategori:** Wajib (Deliverable Data Science)
* **Status:** Terverifikasi, Lengkap 100%, & Terkalibrasi (Siap Analisis & Integrasi Web)
* **Dataset Terkait:** `clean_reservations.csv` (1.214 baris), `synthetic_reservations.csv` (1.214 baris), `raw_reservations_manual.csv`

---

## 1. Deskripsi Umum
Kamus data ini mendeskripsikan spesifikasi skema tabel reservasi sintetis (`clean_reservations.csv`) yang dibangkitkan oleh `generate_synthetic_dataset.py` berdasarkan distribusi statistik dan pola finansial aktual buku kas `Data_tamu.pdf`. Seluruh kolom terisi penuh tanpa nilai kosong (`NaN`), tanpa `Unassigned`, tanpa `"Tidak tercatat"`, dan tanpa mengekspos data pribadi tamu asli.

---

## 2. Struktur Kolom Dataset (`clean_reservations.csv`)

| Nama Kolom | Tipe Data | Format / Contoh | Wajib Diisi | Keterangan & Aturan |
|:---|:---|:---|:---:|:---|
| `reservation_code` | String | `33H-2026-0001` | Ya | Kode identifikasi unik transaksi reservasi (Primary Key). |
| `reservation_group` | String | `Single` | Ya | Penanda grup reservasi. |
| `source_month` | Integer | `1` s.d. `11` | Ya | Bulan transaksi (1 = Januari, 11 = November). |
| `source_table` | String | `kiri`, `kanan` | Ya | Asal kelompok unit: `kiri` (`K1`–`K5`), `kanan` (`TH1`). |
| `source_row_no` | Integer | `1` s.d. `160` | Ya | Nomor urut baris per bulan. |
| `guest_name` | String | `Andi Pratama` | Ya | Nama tamu sintetis (aman privasi, bukan data asli). |
| `guest_city` | String | `Serang`, `Cilegon`, `Tangerang` | Ya | Kota asal tamu sesuai distribusi kota aktual tercatat. |
| `guest_city_source` | String | `synthetic` | Ya | Penanda sumber data sintetis. |
| `guest_phone` | String | `081200000001` | Ya | Nomor telepon sintetis terformat rapi. |
| `room_code` | String | `K1`, `K2`, `K3`, `K4`, `K5`, `TH1` | Ya | Kode unit fisik kamar aktif (6 unit lengkap, tanpa `Unassigned`). |
| `room_type` | String | `Standard Room AC`, `Mezanin`, `Standard Room Kipas`, `Tera House` | Ya | Klasifikasi tipe kamar homestay. |
| `room_code_source` | String | `synthetic` | Ya | Penanda sumber penentuan unit kamar. |
| `unit_scope` | String | `inti`, `teras_house` | Ya | Lingkup unit: `inti` (`K1`–`K5`) dan `teras_house` (`TH1`). |
| `room_capacity` | Integer | `2`, `4`, `6` | Ya | Kapasitas maksimal tamu per unit kamar. |
| `price_per_night` | Integer | `300000`, `350000`, `800000` | Ya | Tarif standar per malam unit kamar (IDR). |
| `check_in` | String (Date) | `2026-08-15` | Ya | Tanggal check-in standar ISO-8601 (`YYYY-MM-DD`). |
| `check_out` | String (Date) | `2026-08-16` | Ya | Tanggal check-out standar ISO-8601 (`YYYY-MM-DD`). |
| `nights` | Integer | `0`, `1`, `2`, `3` | Ya | Durasi menginap dalam hitungan malam (`0` = Sewa Transit / Day-Use). |
| `rental_type` | String | `daily`, `transit` | Ya | `daily` ($\ge 1$ malam) vs `transit` (0 malam / day-use). |
| `total_price` | Integer | `300000` | Ya | Nominal transaksi (IDR), terkalibrasi agar total bulanan sama persis dengan buku kas owner. |
| `reservation_total_price` | Integer | `300000` | Ya | Total tagihan reservasi (IDR). |
| `channel` | String | `WhatsApp`, `Walk-In`, `OTA` | Ya | Saluran masuk reservasi lengkap tanpa label `"Tidak tercatat"`. |
| `ota_raw` | String | `Non-OTA`, `Agoda`, `Traveloka`, `Tiket.com` | Ya | Keterangan platform OTA atau `Non-OTA`. |
| `ota_name` | String | `Non-OTA`, `Agoda`, `Traveloka`, `Tiket.com` | Ya | Nama platform OTA terstandardisasi. |
| `payment_method` | String | `Tunai`, `Transfer Bank`, `QRIS`, `OTA Payment` | Ya | Metode pembayaran transaksi. |
| `guest_count` | Integer | `1` s.d. `6` | Ya | Jumlah tamu (`1 <= guest_count <= room_capacity`). |
| `status` | String | `PAID_OFF` | Ya | Status pembayaran transaksi. |
| `price_text_format` | Boolean | `False` | Ya | Penanda format teks harga. |
| `date_unresolved` | Boolean | `False` | Ya | Penanda tanggal bermasalah (`False` seluruhnya). |
| `needs_review` | Boolean | `False` | Ya | Penanda kebutuhan tinjauan manual (`False` seluruhnya). |
| `notes` | String | `Standar`, `Transit istirahat`, `Check-in malam`, `Setoran ke pengelola` | Ya | Catatan operasional transaksi. |

---

## 3. Matriks Inventaris Kamar & Tarif Resmi (6 Unit Aktif)

| Kode Unit | Nama Kamar | Tipe Kamar | Kapasitas | Tarif Harian | Tarif Transit | Status di Sistem |
|:---:|:---|:---|:---:|:---:|:---:|:---:|
| **K1** | Kamar 1 | Standard Room AC | 2 | Rp 300.000 | Rp 150.000 – Rp 220.000 | Aktif |
| **K2** | Kamar 2 | Standard Room AC | 2 | Rp 300.000 | Rp 150.000 – Rp 220.000 | Aktif |
| **K3** | Kamar 3 (Mezanin) | Mezanin | 4 | Rp 350.000 | Rp 220.000 – Rp 270.000 | Aktif |
| **K4** | Kamar 4 (Kipas) | Standard Room Kipas | 2 | Rp 200.000 | Rp 150.000 | Aktif (Mitra) |
| **K5** | Kamar 5 | Standard Room AC | 2 | Rp 300.000 | Rp 150.000 – Rp 220.000 | Aktif |
| **TH1** | Tera House | Tera House | 6 | Rp 800.000 (basis katalog) | Rp 150.000 – Rp 220.000 | Aktif |
