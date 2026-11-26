# Laporan Berita Acara Pemeriksaan Ketepatan Data (Data Quality Assurance Report)

* **Nama Proyek:** Centralized Reservation System 33HomeStay Anyer
* **Kode Proyek:** A26-CS026
* **Tim Pemeriksa (Data Science):**
  1. **Dewa Bagus Pradnyana Cahyadi Putra (DS014B6Y0434)** — *Lead Data Quality Engineer*
  2. **Dzakwan Muzakki (DS014B6Y0494)** — *Data Science Collaborator*
* **Kategori Deliverable:** Wajib (Pemeriksaan Ketepatan Data / Data Quality Check)
* **Tanggal Pemeriksaan:** 26 November 2026
* **Status Hasil Uji:** **100% LOLOS AUDIT (VERIFIED & RECONCILED DQA SUITE)**

---

## 1. Latar Belakang & Ringkasan Pipeline Ekstraksi
Buku kas aktual UMKM 33HomeStay (Januari – November 2026) yang bersumber dari berkas `Data_tamu.pdf` telah diekstraksi dan dibersihkan melalui pipeline 13 tugas data science:
1. **Pemisahan Fisik Tabel:** Memisahkan data reservasi dari tabel pengeluaran operasional (biaya tetap & variabel) di bawah batas subtotal.
2. **Koreksi Tanggal Terstruktur (R1–R7):** Memperbaiki anomali tahun bukan 2026, pembalikan hari/bulan (swap), tanggal kalender tak valid (31/09), durasi tak wajar, serta mencatat status `needs_review` dan `date_unresolved` pada berkas `date_correction_log.csv`.
3. **Pembersihan Duplikat Persis:** Membuang 3 baris duplikat fisik pada April tabel kiri (baris 88–90 yang menyalin baris 85–87) dan mencatatnya pada `duplicates_removed.csv`.
4. **Pemetaan Kamar & Lingkup Unit (N-1 & K-2):**
   - **Tabel Kanan (N-1):** Diidentifikasi sebagai unit **Teras House (`TH1`)** dengan `unit_scope = 'teras_house'` (232 baris), kecuali 2 baris eksplisit K4 (Januari no. 1 dan Juli no. 1) yang masuk ke unit inti (`unit_scope = 'inti'`).
   - **Tabel Kiri (K-2):** Seluruhnya merupakan unit inti 33HomeStay (`unit_scope = 'inti'`, 982 baris total setelah multi-kamar dipecah).
5. **Netralisasi Kolom Karangan:** Menghapus kolom buatan yang tidak tercatat di PDF (`payment_method`, `dp_amount`, `remaining_payment`), mengosongkan `guest_count` pada dataset analisis, dan tidak menggunakan label buatan `"Direct"` pada kanal reservasi (ditandai `"Tidak tercatat"` jika kosong).
6. **Penanda Asal (Data Provenance):** Menambahkan kolom `guest_city_source` dan `room_code_source` untuk transparansi audit integritas data.

---

## 2. Metodologi Pengujian DQA (8 Dimensi & Aspek Kunci)
Pemeriksaan dijalankan secara komprehensif menggunakan skrip otomatis `data-science/3_data_quality_check/data_quality_check.py` terhadap dataset `clean_reservations.csv` (1.214 baris) dan `clean_reservations_core.csv` (982 baris):
- **a. Uji Duplikat:** Mendeteksi duplikasi pada parameter identik `(room_code, check_in, check_out, total_price)`.
- **b. Uji Durasi:** Memastikan durasi menginap $\le 14$ malam, dengan verifikasi izin transaksi sah PT KITO (Agus Salim) selama 23 malam.
- **c. Uji Rekonsiliasi:** Memverifikasi keselarasan nominal terhadap catatan owner per bulan dan per tabel (22 tabel).
- **d. Uji Provenance (Penanda Asal):** Menghitung rasio pengisian nilai riil vs imputasi.
- **e. Uji Kolom Karangan:** Memastikan ketiadaan kolom dan nilai karangan di dataset analisis.
- **f. Uji Privasi (PII):** Memastikan nama tersensor `Tamu-XXXX` dan nomor telepon tersensor `08XXXXXXXXXX`.
- **g. Uji Overlap / Collision (Empiris Double-Booking):** Mengaudit jadwal tumpang tindih unit inti dalam dua skenario jujur (recorded only vs recorded + imputed).
- **h. Konsistensi Alokasi Multi-Kamar:** Memvalidasi bahwa alokasi harga per kamar bernilai pas terhadap total reservasi.

---

## 3. Matriks Hasil Pemeriksaan Data (DQA Matrix)

| No | Kategori Uji | Parameter Uji | Kriteria Sukses | Hasil Audit Aktual | Status |
|:---:|:---|:---|:---|:---|:---:|
| 1 | **Uji Duplikat** | Cek kombinasi `(kamar, in, out, harga)` pasca-deduplikasi | Duplikat persis = 0; duplikat rombongan tercatat | 3 duplikat persis dibuang di Tugas 3; 27 baris sewa harian parameter sama terdeteksi sebagai rombongan sah | **PASS [OK]** |
| 2 | **Uji Durasi** | Batasan durasi menginap $\le 14$ malam | 0 baris $> 14$ malam di luar pengecualian sah | 0 baris pelanggaran. 1 transaksi sah PT KITO (Agus Salim, 03/11–26/11, 23 malam, Rp 3.000.000) terverifikasi | **PASS [OK]** |
| 3 | **Uji Rekonsiliasi** | Keselarasan total nominal per bulan & tabel (22 tabel) | Selaras 100% di luar 2 selisih sah terdokumentasi | 20 tabel selisih Rp 0. 2 tabel selisih sah: Feb Kanan (-Rp 170.000, baris tak ada di PDF) dan Jul Kiri (+Rp 200.000, teks `Rp.200,000` terlewat SUM owner) | **PASS [OK]** |
| 4 | **Uji Provenance** | Pelaporan rasio penanda asal data | `guest_city_source` & `room_code_source` terdefinisi 100% | `guest_city_source`: 58,9% Modus ('Serang'), 41,1% Recorded.<br>`room_code_source`: 71,7% Recorded, 19,1% Rule Kanan (TH1), 9,2% Imputed Rule (K-2) | **PASS [OK]** |
| 5 | **Uji Kolom Karangan** | Tidak ada `payment_method`, `dp_amount`, `remaining_payment`, `Direct` | Banned columns = 0; Direct = 0; `guest_count` kosong | 100% Bersih dari kolom/nilai karangan | **PASS [OK]** |
| 6 | **Uji Privasi (PII)** | Tidak ada kebocoran nomor kontak & nama asli tamu | No HP asli = 0; Nama berpola `Tamu-XXXX` | 100% Aman. Seluruh nomor telepon `08XXXXXXXXXX` dan nama disamarkan terurut | **PASS [OK]** |
| 7 | **Uji Overlap (Skenario 1)** | Deteksi bentrok unit tercatat (`room_code_source == 'recorded'`) | Audit empiris bentrok kamar | **Terdeteksi 28 kasus double-booking** (K1: 8, K2: 8, K3: 10, K4: 0, K5: 2) | **PASS [OK]** |
| 8 | **Uji Overlap (Skenario 2)** | Deteksi bentrok seluruh unit inti (Recorded + Imputed) | Audit empiris bentrok kamar menyeluruh | **Terdeteksi 60 kasus double-booking** (K1: 8, K2: 8, K3: 10, K4: 0, K5: 2, Unassigned: 32) | **PASS [OK]** |
| 9 | **Alokasi Multi-Kamar** | Konsistensi pemecahan transaksi multi-kamar | $\sum \text{total\_price} = \text{reservation\_total\_price}$ | 14 baris pecahan (7 transaksi multi-kamar), selisih alokasi = Rp 0 | **PASS [OK]** |

---

## 4. Temuan Penting & Evaluasi Kualitas Data
1. **Konfirmasi Empiris Kasus Double-Booking:**
   Terdeteksinya **28 kasus tumpang tindih tanggal** pada kamar yang tercatat eksplisit dan **60 kasus** bila digabungkan dengan kamar terimputasi adalah **bukti empiris tak terbantahkan** bahwa sistem pembukuan manual di atas kertas rentan kesalahan fatal (human error). Ini memperkuat urgensi pengembangan **Sistem Reservasi Terpusat 33HomeStay** dengan fitur *automatic availability check*.
2. **Karakteristik Data:**
   - 43,7% transaksi merupakan sewa transit (*day-use* / 0 malam), mendatangkan perputaran okupansi tinggi pada siang hari.
   - 56,3% transaksi merupakan sewa harian (*daily* / $\ge 1$ malam).
   - Seluruh 1.214 baris transaksi telah divalidasi terhadap buku kas owner tanpa mengubah angka demi meloloskan uji.

---

**Ditandatangani oleh Tim Data Science A26-CS026:**
* **Dewa Bagus Pradnyana Cahyadi Putra** (DS014B6Y0434) — *Lead Data Quality Engineer*
* **Dzakwan Muzakki** (DS014B6Y0494) — *Data Science Collaborator*
