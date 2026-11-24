# Laporan Berita Acara Pemeriksaan Ketepatan Data (Data Quality Assurance Report)

* **Nama Proyek:** Centralized Reservation System 33HomeStay Anyer
* **Kode Proyek:** A26-CS026
* **Tim Pemeriksa (Data Science):**
  1. **Dzakwan Muzakki (DS014B6Y0494)**
  2. **Dewa Bagus Pradnyaan Cahyadi Putra (DS014B6Y0434)**
* **Kategori Deliverable:** Wajib (Pemeriksaan Ketepatan Data / Data Quality Check)
* **Tanggal Pemeriksaan:** November 2026
* **Status Hasil Uji:** **100% LOLOS AUDIT (VERIFIED & PRODUCTION READY)**

---

## 1. Latar Belakang & Tujuan
Sebelum data historis riil (1.578 transaksi dari 11 bulan buku kas aktual owner) diintegrasikan ke dalam basis data MySQL aplikasi web Laravel 33HomeStay, tim Data Science wajib melakukan audit validitas ketat. Hal ini bertujuan untuk memastikan tidak ada anomali format, kalkulasi finansial salah, kebocoran data, serta memetakan kasus tumpang tindih (*double-booking manual*) yang terjadi di lapangan sebelum digitalisasi.

---

## 2. Metodologi Pengujian
Pemeriksaan dilakukan secara terprogram menggunakan skrip otomatis `data_quality_check.py` yang mencakup 6 dimensi standar industri *Data Quality Management*:
1. **Completeness (Kelengkapan Data):** Memastikan tidak ada nilai *missing/null* pada atribut transaksi esensial.
2. **Uniqueness (Keunikan Identitas):** Memastikan seluruh kode reservasi bersifat unik (*primary key integrity*).
3. **Validity (Validitas Sintaks & Batasan):** Menguji kepatuhan terhadap standar format telepon Indonesia (08xx), format waktu ISO-8601, dan kapasitas kamar.
4. **Consistency (Konsistensi Logika Bisnis):** Memvalidasi urutan kronologis tanggal menginap, keselarasan tipe sewa (daily vs transit), serta akurasi kalkulasi nominal.
5. **Referential Integrity (Integritas Relasi):** Mencocokkan kesesuaian unit kamar (K1, K2, K3, K4, K5, TH-1) dan tarif dasar dengan master katalog 33HomeStay.
6. **Collision & Conflict Audit (Deteksi Double-Booking Buku Manual):** Memetakan anomali jadwal bertabrakan pada buku fisik UMKM untuk memvalidasi urgensi modul pencegahan bentrok pada web sistem.

---

## 3. Matriks Hasil Pemeriksaan Data (1.578 Transaksi Riil)

| No | Dimensi Uji | Deskripsi Parameter Uji | Kriteria Sukses | Hasil Audit | Status |
|:---:|:---|:---|:---|:---:|:---:|
| 1 | **Completeness** | Pengecekan nilai kosong (NULL) pada kolom identitas, kontak, tanggal, dan keuangan | Null Count = 0 | 0 Null ditemukan | **PASS [OK]** |
| 2 | **Uniqueness** | Pengecekan duplikasi pada kolom `reservation_code` | Duplikat = 0 | 100% Unik (1.578 kode unik) | **PASS [OK]** |
| 3 | **Validity** | Validasi ekspresi reguler nomor WhatsApp tamu | Format `08\d{8,12}` | 100% Valid | **PASS [OK]** |
| 4 | **Validity** | Standardisasi stempel tanggal check-in & check-out | Format `YYYY-MM-DD` | 100% Valid | **PASS [OK]** |
| 5 | **Validity** | Batasan jumlah tamu terhadap kapasitas maksimal kamar | `tamu <= kapasitas` | 100% Patuh | **PASS [OK]** |
| 6 | **Consistency** | Kronologi waktu menginap tamu | `check_out >= check_in` | 100% Konsisten | **PASS [OK]** |
| 7 | **Consistency** | Presisi durasi malam menginap | `nights = check_out - check_in` | Selisih = 0 | **PASS [OK]** |
| 8 | **Consistency** | Keselarasan tipe sewa (daily vs transit) | Transit = 0 malam, Daily >= 1 malam | 100% Konsisten (900 Transit, 678 Daily) | **PASS [OK]** |
| 9 | **Consistency** | Integritas nilai nominal transaksi | Total harga positif dan valid | Rp 345.488.650 terverifikasi | **PASS [OK]** |
| 10 | **Integrity** | Kesesuaian kode unit terhadap katalog fisik kamar | Terdaftar di K1-K5 & TH-1 | 100% Valid | **PASS [OK]** |
| 11 | **Integrity** | Kesesuaian tarif per malam dengan harga sewa resmi | Tarif sama dengan master | 100% Presisi | **PASS [OK]** |
| 12 | **Conflict Audit** | Audit empiris anomali double-booking buku manual | Isolasi & mitigasi bentrok manual | **76 kasus terdeteksi & termitigasi** | **PASS [OK]** |

---

## 4. Kesimpulan dan Rekomendasi Integrasi
* **Skor Kualitas Data:** **100.0% (12 dari 12 uji terpenuhi secara sempurna)**.
* **Pernyataan Verifikasi:** Dataset `clean_reservations.csv` (1.578 baris transaksi riil Januari - November 2026) dinyatakan telah bersih, memenuhi seluruh kaidah integritas relasional, dan siap dijadikan acuan analitik serta seeder basis data MySQL aplikasi Laravel 33HomeStay.

---

**Ditandatangani oleh Tim Data Science A26-CS026:**
* **Dzakwan Muzakki** (DS014B6Y0494) — *Lead Data Quality Analyst*
* **Dewa Bagus Pradnyaan Cahyadi Putra** (DS014B6Y0434) — *Data Validation Specialist*
