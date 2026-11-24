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
Sebelum data dummy dan data historis diintegrasikan ke dalam basis data SQLite aplikasi web Laravel 33HomeStay, tim Data Science wajib melakukan audit validitas ketat. Hal ini bertujuan untuk memastikan tidak ada anomali format, kalkulasi finansial salah, kebocoran data, maupun potensi *double booking* terselubung yang dapat merusak integritas sistem operasional penginapan.

---

## 2. Metodologi Pengujian
Pemeriksaan dilakukan secara terprogram menggunakan skrip otomatis `data_quality_check.py` yang mencakup 6 dimensi standar industri *Data Quality Management*:
1. **Completeness (Kelengkapan Data):** Memastikan tidak ada nilai *missing/null* pada atribut transaksi esensial.
2. **Uniqueness (Keunikan Identitas):** Memastikan seluruh kode reservasi bersifat unik (*primary key integrity*).
3. **Validity (Validitas Sintaks & Batasan):** Menguji kepatuhan terhadap standar format telepon Indonesia, format waktu ISO-8601, dan kapasitas kamar.
4. **Consistency (Konsistensi Logika Bisnis):** Memvalidasi urutan kronologis tanggal menginap serta akurasi rumus kalkulasi total tarif.
5. **Referential Integrity (Integritas Relasi):** Mencocokkan kesesuaian unit kamar dan tarif dasar dengan master katalog 33HomeStay.
6. **Anti-Double-Booking Verification (Audit Bentrok Jadwal):** Memeriksa seluruh rentang waktu `[check_in, check_out)` per unit kamar secara berpasangan untuk memastikan nol tumpang tindih waktu.

---

## 3. Matriks Hasil Pemeriksaan Data

| No | Dimensi Uji | Deskripsi Parameter Uji | Kriteria Sukses | Hasil Audit | Status |
|:---:|:---|:---|:---|:---:|:---:|
| 1 | **Completeness** | Pengecekan nilai kosong (NULL) pada kolom identitas, kontak, tanggal, dan keuangan | Null Count = 0 | 0 Null ditemukan | **PASS [OK]** |
| 2 | **Uniqueness** | Pengecekan duplikasi pada kolom `reservation_code` | Duplikat = 0 | 100% Unik | **PASS [OK]** |
| 3 | **Validity** | Validasi ekspresi reguler nomor WhatsApp tamu | Format `08\d{8,12}` | 100% Valid | **PASS [OK]** |
| 4 | **Validity** | Standardisasi stempel tanggal check-in & check-out | Format `YYYY-MM-DD` | 100% Valid | **PASS [OK]** |
| 5 | **Validity** | Batasan jumlah tamu terhadap kapasitas maksimal kamar | `tamu <= kapasitas` | 100% Patuh | **PASS [OK]** |
| 6 | **Consistency** | Kronologi waktu menginap tamu | `check_out > check_in` | 100% Konsisten | **PASS [OK]** |
| 7 | **Consistency** | Presisi durasi malam menginap | `nights = check_out - check_in` | Selisih = 0 | **PASS [OK]** |
| 8 | **Consistency** | Integritas nilai nominal transaksi | `total = price_night x nights` | Selisih = 0 | **PASS [OK]** |
| 9 | **Integrity** | Kesesuaian kode unit terhadap katalog fisik kamar | Terdaftar di 6 unit resmi | 100% Valid | **PASS [OK]** |
| 10 | **Integrity** | Kesesuaian tarif per malam dengan harga sewa resmi | Tarif sama dengan master | 100% Presisi | **PASS [OK]** |
| 11 | **Anti-Double-Booking** | Verifikasi interval jadwal tidak saling tumpang tindih | Nol bentrok tanggal per kamar | **0 Bentrok** | **PASS [OK]** |

---

## 4. Kesimpulan dan Rekomendasi Integrasi
* **Skor Kualitas Data:** **100.0% (11 dari 11 uji terpenuhi secara sempurna)**.
* **Pernyataan Verifikasi:** Dataset `clean_reservations.csv` dinyatakan telah bersih, memenuhi seluruh kaidah integritas relasional, bebas dari risiko pemesanan ganda, dan disetujui untuk digunakan sebagai sumber seeder basis data aplikasi Laravel 33HomeStay.

---

**Ditandatangani oleh Tim Data Science A26-CS026:**
* **Dzakwan Muzakki** (DS014B6Y0494) — *Lead Data Quality Analyst*
* **Dewa Bagus Pradnyaan Cahyadi Putra** (DS014B6Y0434) — *Data Validation Specialist*
