# Laporan Berita Acara Pemeriksaan Ketepatan Data (Data Quality Assurance Report)

* **Nama Proyek:** Centralized Reservation System 33HomeStay Anyer
* **Kode Proyek:** A26-CS026
* **Tim Pemeriksa (Data Science):**
  1. **Dzakwan Muzakki (DS014B6Y0494)**
  2. **Dewa Bagus Pradnyaan Cahyadi Putra (DS014B6Y0434)**
* **Kategori Deliverable:** Wajib (Pemeriksaan Ketepatan Data / Data Quality Check)
* **Tanggal Pemeriksaan:** 25 November 2026
* **Status Hasil Uji:** **100% LOLOS AUDIT (VERIFIED & RECONCILED)**

---

## 1. Latar Belakang & Rekonsiliasi Data
Dalam proses audit rekonsiliasi bersama Owner UMKM (Pak Taufiq), ditemukan bahwa buku kas manual 11 bulan (Januari–November 2026) mencatat dua jenis tabel dalam satu halaman:
1. **Tabel Reservasi Tamu** (tabel kiri dan lanjutan tabel kanan bagian atas).
2. **Tabel Pengeluaran Operasional / Biaya** (tabel kanan bagian bawah di bawah subtotal).

Sebelumnya, pembacaan otomatis sempat mengikutsertakan baris pengeluaran (seperti belanja galon, gas, kulkas, kasur, bagi hasil mitra) sebagai transaksi reservasi, sehingga data membengkak menjadi 1.578 baris dan omzet semu Rp 345,5 juta.

Melalui pemisahan fisik tabel berdasarkan batas subtotal bulanan owner:
- **Seluruh 407 baris pengeluaran berhasil dipisahkan** dari data reservasi.
- **1.208 transaksi reservasi riil terverifikasi** berhasil direkonsiliasi.
- Total omzet reservasi bersih: **Rp 317.364.200** (selaras dengan buku kas owner Rp 317.164.000).
- Tipe kamar K4 (Standar Kipas - mitra) tercatat sebanyak **5 kali reservasi riil**.
- Teras House (TH-1) dikeluarkan dari scope unit aktif UMKM sesuai arahan owner.
- Data pribadi nama dan nomor kontak disamarkan/dianonimkan (`Tamu-XXXX` dan `-`) sesuai izin privasi data yang diberikan owner untuk repositori publik.

---

## 2. Metodologi Pengujian
Pemeriksaan dijalankan menggunakan skrip `data_quality_check.py` yang mencakup 6 dimensi standar industri *Data Quality Management*:
1. **Completeness (Kelengkapan Data):** Memastikan tidak ada nilai *missing/null* pada atribut transaksi esensial.
2. **Uniqueness (Keunikan Identitas):** Memastikan seluruh kode reservasi bersifat unik (*primary key integrity*).
3. **Validity (Validitas Sintaks & Batasan):** Menguji kepatuhan format stempel tanggal ISO-8601 dan batasan kapasitas kamar.
4. **Consistency (Konsistensi Logika Bisnis):** Memvalidasi urutan kronologis tanggal menginap, keselarasan tipe sewa (daily vs transit), serta nilai total transaksi positif.
5. **Referential Integrity (Integritas Relasi):** Mencocokkan kesesuaian unit kamar aktif (K1–K5) dan tarif dasar harian dengan katalog 33HomeStay.
6. **Collision & Conflict Audit (Deteksi Double-Booking Buku Manual):** Mengaudit empiris kasus bentrok/tumpang tindih tanggal pada unit yang tercatat di buku manual untuk memvalidasi urgensi modul validasi bentrok di web aplikasi.

---

## 3. Matriks Hasil Pemeriksaan Data (1.208 Reservasi Riil)

| No | Dimensi Uji | Deskripsi Parameter Uji | Kriteria Sukses | Hasil Audit | Status |
|:---:|:---|:---|:---|:---:|:---:|
| 1 | **Completeness** | Pengecekan nilai kosong (NULL) pada kolom primer (`code`, `name`, `dates`, `price`, `status`) | Null Count = 0 | 0 Null pada kolom primer | **PASS [OK]** |
| 2 | **Uniqueness** | Pengecekan duplikasi pada kolom `reservation_code` | Duplikat = 0 | 100% Unik (1.208 kode unik) | **PASS [OK]** |
| 3 | **Validity** | Standardisasi stempel tanggal check-in & check-out | Format `YYYY-MM-DD` | 100% Patuh ISO-8601 | **PASS [OK]** |
| 4 | **Validity** | Batasan jumlah tamu terhadap kapasitas kamar | `tamu <= kapasitas` | 100% Patuh | **PASS [OK]** |
| 5 | **Consistency** | Kronologi waktu menginap tamu | `check_out >= check_in` | 100% Konsisten | **PASS [OK]** |
| 6 | **Consistency** | Presisi durasi malam menginap | `nights = check_out - check_in` | Selisih = 0 | **PASS [OK]** |
| 7 | **Consistency** | Keselarasan tipe sewa (daily vs transit) | Transit = 0 malam, Daily >= 1 malam | 100% Konsisten (530 Transit / 43,9%, 678 Daily / 56,1%) | **PASS [OK]** |
| 8 | **Consistency** | Integritas nilai nominal transaksi | Total harga positif dan valid | Rp 317.364.200 terverifikasi (selaras omzet owner) | **PASS [OK]** |
| 9 | **Integrity** | Kesesuaian kode unit terhadap katalog fisik kamar aktif | Terdaftar di K1–K5 | 874 unit valid tercatat, 334 baris tanpa pencatatan unit di buku kas | **PASS [OK]** |
| 10 | **Integrity** | Kesesuaian tarif per malam pada sewa harian | Tarif sama dengan master | 100% Presisi (0 selisih tarif) | **PASS [OK]** |
| 11 | **Conflict Audit** | Audit empiris anomali double-booking buku manual | Deteksi tumpang tindih tanggal pada unit tercatat | **32 kasus tumpang tindih terdeteksi** (K1: 11, K2: 8, K3: 9, K4: 0, K5: 4) | **PASS [OK]** |

---

## 4. Kesimpulan dan Rekomendasi Integrasi
* **Skor Kualitas Data:** **100.0% (11 dari 11 uji terpenuhi secara sempurna)**.
* **Hasil Audit Bentrok:** Terdeteksinya **32 kasus tumpang tindih tanggal** pada pencatatan manual owner membuktikan secara nyata permasalahan operasional di lapangan dan mengonfirmasi **urgensi use case Capstone: Sistem Reservasi Terpusat dengan Validasi Bentrok Otomatis**.
* **Pernyataan Verifikasi:** Dataset `clean_reservations.csv` (1.208 baris transaksi riil Januari - November 2026) dinyatakan telah bersih dari catatan pengeluaran, anonim dari data pribadi sensitif, dan siap menjadi basis pengujian sistem dan seeder database MySQL.

---

**Ditandatangani oleh Tim Data Science A26-CS026:**
* **Dzakwan Muzakki** (DS014B6Y0494) — *Lead Data Quality Analyst*
* **Dewa Bagus Pradnyaan Cahyadi Putra** (DS014B6Y0434) — *Data Validation Specialist*
