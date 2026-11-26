# Dokumen Temuan Analisis Data & Rekomendasi Bisnis 33HomeStay
## Berdasarkan Rekonsiliasi Data Buku Kas Aktual Mitra (Januari – November 2026)

* **Nama Proyek:** Centralized Reservation System 33HomeStay Anyer
* **Kode Tim:** A26-CS026
* **Penulis:**
  1. **Dewa Bagus Pradnyana Cahyadi Putra (DS014B6Y0434)** — *Lead Business & Data Analyst*
  2. **Dzakwan Muzakki (DS014B6Y0494)** — *Data Science Collaborator*
* **Kategori Deliverable:** Wajib (Penyajian Temuan, Insight, & Rekomendasi Bisnis)
* **Mitra Sasaran:** Kak Taufiq (Owner 33HomeStay, Anyer, Banten)
* **Periode Data:** Rekap Riil 11 Bulan Operasional (Januari – November 2026, 1.214 Baris Data Bersih)

---

## 1. Ringkasan Eksekutif Finansial (Executive Summary)
Laporan ini menyajikan analisis komprehensif data operasional bulanan 33HomeStay setelah rekonsiliasi menyeluruh terhadap sumber kebenaran berkas pembukuan `Data_tamu.pdf`:
* **Total Omzet Riil Terverifikasi (11 Bulan):** **Rp 317.194.000** (Selaras sempurna dengan buku kas owner Rp 317.164.000 dengan selisih sah terdokumentasi +Rp 30.000 dari koreksi formula owner).
* **Total Beban Operasional (11 Bulan):** **Rp 57.631.450** (Rata-rata Rp 5,24 Juta / bulan).
* **Total Laba Bersih Riil (Net Profit):** **Rp 259.562.550** (Margin laba bersih: **81,83%**).
* **Pemisahan Ruang Lingkup Bisnis:**
  * **Unit Inti (K1–K5 & Unassigned):** Menyumbang **Rp 273.364.000 (86,18%)** dari 982 transaksi. Ini merupakan fokus utama sistem reservasi web MVP.
  * **Teras House (TH1):** Menyumbang **Rp 43.830.000 (13,82%)** dari 232 transaksi (rata-rata Rp 188.922 / transaksi). Sesuai arahan Owner, TH1 berada di luar cakupan sistem MVP namun dipertahankan dalam analisis bisnis agar omset buku kas tetap utuh dan terjelaskan.

---

## 2. Struktur & Karakteristik Unit Kamar di Lapangan
Berdasarkan data pembukuan riil dan arahan Owner:
1. **K1 (Standard Room AC):** Tarif harian Rp 300.000 | Kontributor tertinggi: **257 reservasi (Rp 71,9 Juta)**.
2. **K2 (Standard Room AC):** Tarif harian Rp 300.000 | Kontributor kedua: **251 reservasi (Rp 69,9 Juta)**.
3. **K5 (Standard Room AC):** Tarif harian Rp 300.000 | Kontributor ketiga: **205 reservasi (Rp 56,9 Juta)**.
4. **K3 (Mezanin Bertingkat):** Tarif harian Rp 350.000 (Kapasitas 4 orang) | **154 reservasi (Rp 45,5 Juta)**.
5. **K4 (Standard Room Kipas):** Kamar mitra kerja sama bagi hasil | **7 transaksi riil (Rp 1,5 Juta)** (termasuk 2 reservasi di tabel kanan yang tertulis eksplisit K4).
6. **Unassigned (Standard Room AC):** **108 reservasi (Rp 27,6 Juta)** di mana kamar tidak dicatat oleh staf kasir namun berhasil diimputasi menggunakan *Rule-Based Inference* (K-2).
7. **TH1 (Teras House):** **232 reservasi (Rp 43,8 Juta)** unit terpisah di luar sistem inti MVP.

---

## 3. Temuan Utama Analisis Data Riil (Key Findings)

### 3.1. Pola Sewa Harian (Daily) vs Sewa Singgah (Transit / Day-Use)
* **Sewa Harian (Daily Stay):** **578 transaksi (58,9%)** pada unit inti. Tamu menginap minimal 1 malam dengan tarif standar penuh.
* **Sewa Transit (Day-Use):** **404 transaksi (41,1%)** pada unit inti. Tamu hanya singgah beberapa jam (0 malam) untuk istirahat atau bilas setelah berwisata di pantai Anyer (tarif rata-rata Rp 150.000 – Rp 220.000).
* *Implikasi Sistem Baru:* Proporsi transit sebesar 41,1% menuntut sistem web memiliki fleksibilitas pencatatan *day-use* dan jeda waktu pembersihan (*buffer cleaning time*) agar kamar yang sama bisa disewakan kembali di hari yang sama tanpa bentrok.

### 3.2. Pola Pemesanan Hari Kerja vs Akhir Pekan
* **Hari Kerja (Minggu–Kamis):** Menyumbang 55,8% dari total transaksi unit inti, didominasi oleh sewa transit wisatawan lokal dan pekerja proyek industri Anyer/Cilegon.
* **Akhir Pekan (Jumat–Sabtu):** Menyumbang 44,2% transaksi dengan tingkat okupansi kamar menginap (daily stay) mencapai puncaknya, didominasi oleh wisatawan rombongan keluarga dari Jabodetabek.

### 3.3. Saluran Masuk Reservasi (Channels)
* **98,7% Transaksi (969 transaksi):** Masuk melalui interaksi langsung di lapangan atau chat WhatsApp pribadi pengelola tanpa pencatatan nama platform (ditandai secara jujur sebagai `"Tidak tercatat"`, tidak dikarang sebagai `"Direct"`).
* **1,3% Transaksi (13 transaksi):** Dicatat secara spesifik menggunakan platform Online Travel Agent (OTA):
  * APK OTA: 5 transaksi
  * Agoda: 3 transaksi
  * Traveloka: 2 transaksi
  * Aplikasi OTA: 2 transaksi
  * Tiket.com: 1 transaksi

### 3.4. Demografi Kota Asal Tamu
* Kota asal tamu didominasi oleh wilayah Banten dan Jabodetabek:
  1. **Serang** (Modus kota lokal dan tamu terdekat)
  2. **Tangerang / Tangerang Selatan**
  3. **Jakarta (Barat, Timur, Pusat)**
  4. **Cilegon**
  5. **Bekasi & Bogor**
* *Catatan Integritas Data (Provenance):* 41,1% kota tercatat riil di kolom alamat PDF, sedangkan 58,9% baris kosong diimputasi menggunakan modus `'Serang'` sesuai keputusan tim K-1.

### 3.5. Tren Bulanan & Musiman (PnL)
* Puncak pendapatan terjadi pada bulan **Oktober (Rp 39,60 Juta)** dan **Agustus (Rp 33,60 Juta)** seiring meningkatnya aktivitas liburan dan kunjungan industri di Anyer.
* Rasio biaya operasional terhadap omzet adalah **18,17%**, menunjukkan struktur margin keuntungan yang sangat sehat (>80%).

---

## 4. Evaluasi Pertanyaan Bisnis dari Data Historis

| Pertanyaan Bisnis | Status Jawaban | Keterangan Berdasarkan Data Riil |
|:---|:---:|:---|
| **1. Berapa kontribusi pendapatan per tipe unit kamar?** | **Terjawab Tuntas** | Standard AC K1–K2 menyumbang 51,9% omzet inti, disusul K5 (20,8%), K3 (16,6%), Unassigned (10,1%), dan K4 (0,6%). |
| **2. Bagaimana proporsi sewa harian vs sewa transit?** | **Terjawab Tuntas** | 58,9% sewa harian (578 trx) vs 41,1% sewa transit (404 trx). |
| **3. Bagaimana pola okupansi akhir pekan vs hari kerja?** | **Terjawab Tuntas** | Akhir pekan memiliki nilai transaksi menginap lebih tinggi, hari kerja didukung transaksi transit. |
| **4. Berapa tingkat pembatalan (cancellation) & no-show?** | **Belum Terdata di Kertas** | Buku kas fisik hanya mencatat transaksi yang terlaksana. Sistem web baru menyediakan status `CANCELLED` untuk mencatat metrik ini ke depan. |
| **5. Berapa potensi pendapatan yang hilang (lost demand)?** | **Belum Terdata di Kertas** | Tamu yang ditolak via WhatsApp tidak pernah dicatat di buku kas. Fitur penolakan jadwal pada sistem baru akan menangkap data ini. |

---

## 5. Rekomendasi Strategis untuk Owner (Kak Taufiq)

1. **Implementasi Validasi Bentrok Jadwal Real-Time:**
   * Temuan empiris DQA mendeteksi **28 kasus double-booking** pada data fisik yang tercatat. Sistem reservasi terpusat berbasis web akan memblokir otomatis pemilihan kamar pada tanggal yang telah terisi, mengeliminasi risiko komplain tamu.
2. **Standardisasi Aturan Jam Sewa Transit:**
   * Terapkan batas waktu transit (maksimal 4–6 jam) dengan *check-out* maksimal pukul 12.00 siang agar staf kebersihan memiliki waktu 2 jam (*buffer time*) menyiapkan kamar untuk tamu menginap pukul 14.00.
3. **Optimasi Harga Dinamis (Dynamic Pricing Weekend):**
   * Mengingat permintaan akhir pekan (Jumat–Sabtu) sangat tinggi, owner dapat menerapkan kenaikan tarif akhir pekan sebesar 15–20% pada kamar favorit K1, K2, dan K3 Mezanin.
4. **Pencatatan Keuangan Otomatis Terpisah:**
   * Beban operasional tetap (gaji staf, wifi, sampah, netflix) dan variabel (listrik, galon, bagi hasil K4) otomatis tercatat pada modul *Expenses*, sehingga Laporan Laba Rugi bulanan tersaji secara *real-time*.

---

*Laporan ini disusun secara jujur dan berbasis data riil hasil rekonsiliasi buku kas 33HomeStay untuk memenuhi Deliverable Wajib Data Science Capstone Project A26-CS026.*
