# Kamus Data (Data Dictionary) - 33HomeStay Reservation Data
## Berdasarkan Rekonsiliasi 11 Bulan Buku Kas Riil UMKM (Januari – November 2026)

* **Nama Proyek:** Centralized Reservation System 33HomeStay Anyer
* **ID Proyek:** A26-CS026
* **Penanggung Jawab:**
  1. **Dewa Bagus Pradnyana Cahyadi Putra (DS014B6Y0434)** — *Lead Data Science Engineer*
  2. **Dzakwan Muzakki (DS014B6Y0494)** — *Data Science Collaborator*
* **Kategori:** Wajib (Deliverable Data Science)
* **Status:** Terverifikasi, Bersih, & Terekonsiliasi (100% Siap Integrasi)
* **Dataset Terkait:** `clean_reservations.csv` (1.214 baris), `clean_reservations_core.csv` (982 baris), `raw_reservations_manual.csv`

---

## 1. Deskripsi Umum
Kamus data ini mendeskripsikan spesifikasi skema data tabel reservasi yang telah melalui proses ekstraksi akurat, pembersihan tanggal terstruktur (R1–R7), deduplikasi, pemetaan kamar objektif, netralisasi kolom karangan, dan penambahan penanda asal (*provenance tracking*) dari berkas pembukuan kasir `Data_tamu.pdf`.

---

## 2. Struktur Kolom Dataset (`clean_reservations.csv`)

| Nama Kolom | Tipe Data | Format / Contoh | Wajib Diisi | Nilai Kosong / Imputasi | Deskripsi Bisnis & Ketentuan |
|:---|:---|:---|:---:|:---|:---|
| `reservation_code` | String | `33H-2026-0001` | Ya | Tidak ada | Kode identifikasi unik transaksi reservasi (Primary Key). |
| `reservation_group` | String | `GRP-2026-0019` | Tidak | Kosong jika single room | Kode kelompok untuk reservasi multi-kamar yang dipecah per kamar. |
| `source_month` | Integer | `1` s.d. `11` | Ya | Tidak ada | Bulan lembar kerja buku kas (1 = Januari, 11 = November). |
| `source_table` | String | `kiri`, `kanan` | Ya | Tidak ada | Posisi tabel fisik pada PDF: `kiri` (Unit Inti), `kanan` (Teras House / TH1). |
| `source_row_no` | String | `1`, `45`, `b` | Tidak | Kosong bila tak bernomor | Nomor urut baris asli pada rekap kasir buku tamu. |
| `guest_name` | String | `Tamu-0001` | Ya | Disensor | Nama tamu disamarkan berurutan demi mematuhi privasi data (GDPR/UU PDP). |
| `guest_city` | String | `Serang`, `Tangerang` | Ya | Imputasi Modus (K-1) | Kota asal tamu. Bila tidak tertulis di PDF, diisi modus (`'Serang'`). |
| `guest_city_source` | String | `recorded`, `imputed_mode` | Ya | Tidak ada | Penanda asal: `recorded` (41,1% terdata di PDF) vs `imputed_mode` (58,9% diisi modus). |
| `guest_phone` | String | `08XXXXXXXXXX` | Ya | Data Masking | Nomor telepon disensor seragam untuk mencegah kebocoran PII. |
| `room_code` | String | `K1`, `K2`, `K3`, `K4`, `K5`, `TH1`, `Unassigned` | Ya | Rule-Based (K-2 / N-1) | Kode unit fisik kamar yang dipesan. |
| `room_type` | String | `Standard Room AC`, `Mezanin`, `Standard Room Kipas`, `Teras House` | Ya | Rule-Based | Klasifikasi tipe kamar homestay. |
| `room_code_source` | String | `recorded`, `rule_right_table`, `imputed_rule` | Ya | Tidak ada | Penanda asal penentuan kamar: `recorded` (71,7%), `rule_right_table` (19,1%), `imputed_rule` (9,2%). |
| `unit_scope` | String | `inti`, `teras_house` | Ya | Tidak ada | Lingkup unit: `inti` (unit operasional MVP) vs `teras_house` (unit luar MVP). |
| `room_capacity` | Float | `2.0`, `4.0` | Tidak | Kosong (NaN) pada TH1 | Kapasitas maksimal tamu per kamar. TH1 dikosongkan karena tidak diketahui di PDF. |
| `price_per_night` | Float | `300000.0`, `350000.0` | Tidak | Kosong (NaN) pada TH1 | Tarif resmi per malam. Dikosongkan pada TH1 (hanya tercatat total transaksi). |
| `check_in` | String (Date) | `2026-08-15` | Tidak | Kosong jika unresolved | Tanggal check-in standar ISO-8601 (`YYYY-MM-DD`). |
| `check_out` | String (Date) | `2026-08-16` | Tidak | Kosong jika unresolved | Tanggal check-out standar ISO-8601 (`YYYY-MM-DD`). |
| `nights` | Integer | `0`, `1`, `2` | Ya | `0` untuk transit/unresolved | Durasi menginap dalam hitungan malam. 0 = Sewa Transit (Day-Use). |
| `rental_type` | String | `daily`, `transit` | Ya | Tidak ada | `daily` (durasi $\ge 1$ malam) vs `transit` (durasi 0 malam / day-use). |
| `total_price` | Integer | `300000` | Ya | `> 0` | Nominal transaksi per baris kamar (IDR). |
| `reservation_total_price` | Integer | `600000` | Ya | `> 0` | Total tagihan utuh reservasi sebelum dipecah multi-kamar. |
| `channel` | String | `OTA`, `Tidak tercatat` | Ya | Tidak tercatat | Saluran pemesanan. Bila tidak tercatat di PDF, ditandai `"Tidak tercatat"` (bukan `"Direct"`). |
| `ota_raw` | String | `agoda`, `tiket.com`, `opsi 2` | Tidak | Kosong jika bukan OTA | Nilai teks mentah pada kolom OTA di rekap kasir. |
| `ota_name` | String | `Agoda`, `Tiket.com`, `Traveloka` | Tidak | Kosong jika bukan OTA | Nama platform OTA terstandardisasi. |
| `status` | String | `PAID_OFF` | Ya | Default `PAID_OFF` | Status pembayaran transaksi (seluruh transaksi di buku kas telah lunas). |
| `price_text_format` | Boolean | `True`, `False` | Ya | `False` | `True` bila di PDF harga ditulis sebagai teks seperti `"Rp.200,000"`. |
| `date_unresolved` | Boolean | `True`, `False` | Ya | `False` | `True` bila tanggal ambigu/tidak tercatat (kasus Candra Juni & Nur Januari). |
| `needs_review` | Boolean | `True`, `False` | Ya | `False` | `True` jika ada ambiguitas durasi/tanggal yang memerlukan verifikasi owner. |
| `notes` | String | `Transit; Format harga teks` | Tidak | Kosong bila tidak ada catatan | Catatan operasional aktual dari buku kas dan sistem. |

---

## 3. Kebijakan Netralisasi Kolom Karangan (Tugas 5)
Sesuai prinsip integritas ilmiah dan arahan mitra:
1. **`guest_count`:** Dikosongkan (NaN) pada dataset analisis karena staf kasir tidak pernah mencatat jumlah orang per transaksi di buku kas fisik. (Hanya diisi default kapasitas pada seeder database).
2. **`payment_method`, `dp_amount`, `remaining_payment`:** **Dihapus total** dari dataset analisis. Kasir hanya mencatat total nominal final, tidak mencatat bukti transfer BCA maupun pembagian DP 50%.
3. **Kanal `Direct`:** **Dilarang keras**. Baris tanpa keterangan OTA ditandai `"Tidak tercatat"` karena kasir tidak pernah membedakan apakah tamu datang via Walk-In, telepon, atau rekomendasi teman.

---

## 4. Matriks Inventaris Kamar & Tarif Resmi

| Kode Unit | Nama Kamar | Tipe Kamar | Kapasitas | Tarif Harian | Tarif Transit | Lingkup Sistem |
|:---:|:---|:---|:---:|:---:|:---:|:---:|
| **K1** | Kamar 1 | Standard Room AC | 2 | Rp 300.000 | Rp 150.000 – Rp 220.000 | Unit Inti (MVP) |
| **K2** | Kamar 2 | Standard Room AC | 2 | Rp 300.000 | Rp 150.000 – Rp 220.000 | Unit Inti (MVP) |
| **K3** | Kamar 3 (Mezanin) | Mezanin Bertingkat | 4 | Rp 350.000 | Rp 200.000 – Rp 220.000 | Unit Inti (MVP) |
| **K4** | Kamar 4 (Kipas) | Standard Room Kipas | 2 | Rp 200.000 | Rp 100.000 – Rp 150.000 | Unit Inti (Mitra) |
| **K5** | Kamar 5 | Standard Room AC | 2 | Rp 300.000 | Rp 150.000 – Rp 220.000 | Unit Inti (MVP) |
| **Unassigned**| Kamar Tak Dicatat | Standard Room AC | 2 | Rp 300.000 | Rp 150.000 – Rp 220.000 | Unit Inti (Imputasi) |
| **TH1** | Teras House | Teras House | - | Fleksibel | Fleksibel | Luar Ruang Lingkup MVP |
