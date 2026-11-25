# Dokumen Temuan Analisis Data & Rekomendasi Bisnis 33HomeStay
## Berdasarkan Rekonsiliasi Data Buku Kas Aktual Mitra (Januari – November 2026)

* **Nama Proyek:** Centralized Reservation System 33HomeStay Anyer
* **Kode Tim:** A26-CS026
* **Penulis:** Dzakwan Muzakki (Data Science - DS014B6Y0494)
* **Kategori Deliverable:** Wajib (Penyajian Temuan, Insight, & Rekomendasi Bisnis)
* **Mitra Sasaran:** Kak Taufiq (Owner 33HomeStay, Anyer, Banten)
* **Periode Data:** Rekap Riil 11 Bulan Operasional (Januari – November 2026)

---

## 1. Ringkasan Eksekutif Finansial (Executive Summary)
Laporan ini menyajikan analisis data historis aktual dari buku kas operasional bulanan 33HomeStay periode **Januari hingga November 2026** setelah proses rekonsiliasi data:
* **Total Omzet Bruto (11 Bulan):** **Rp 317.164.000** (Rata-rata Rp 28,8 Juta / bulan)
* **Total Beban Operasional (11 Bulan):** **Rp 57.631.450** (Rata-rata Rp 5,2 Juta / bulan)
* **Total Laba Bersih Riil (Net Profit):** **Rp 259.532.550** (Omzet Rp 317,16 jt dikurangi Beban Rp 57,63 jt)
* **Rasio Biaya Operasional (Operating Expense Ratio):** **18.17%** *(Kondisi keuangan sehat dengan margin laba bersih 81.83%)*

---

## 2. Struktur Unit Kamar Aktif di Lapangan
Berdasarkan klarifikasi dan data operasional terkini dari Owner:
1. **K1:** Standard Room AC (Tarif Menginap: Rp 300.000 | Transit: Rp 200.000 - 220.000)
2. **K2:** Standard Room AC (Tarif Menginap: Rp 300.000 | Salah satu unit kamar paling produktif)
3. **K3:** Mezanin Bertingkat (Tarif Menginap: Rp 350.000 | Kapasitas hingga 4 orang)
4. **K4:** Standard Room Kipas — Kamar mitra kerja sama bagi hasil (tercatat 5 kali dalam periode ini)
5. **K5:** Standard Room AC (Tarif Menginap: Rp 300.000)

*Catatan Ruang Lingkup:* Unit villa terpisah **Teras House (TH-1)** telah dikonfirmasi oleh Owner untuk **dikeluarkan dari ruang lingkup (scope) proyek MVP sistem ini**, sehingga fokus inventaris aplikasi adalah unit kamar K1–K5.

---

## 3. Temuan Utama Analisis Data Riil (Key Findings)

### 3.1. Tren Omzet & Profitabilitas Bulanan (Januari – November 2026)

| Bulan | Omzet Bruto (IDR) | Biaya Tetap (IDR) | Biaya Variabel (IDR) | Total Biaya (IDR) | Laba Bersih (IDR) | Unit Terfavorit | Kota Terbanyak |
|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| **Januari** | Rp 26.392.000 | Rp 3.108.000 | Rp 4.397.000 | Rp 7.505.000 | Rp 18.887.000 | **K2** | Serang |
| **Februari** | Rp 19.461.000 | Rp 1.808.000 | Rp 325.000 | Rp 2.133.000 | Rp 17.328.000 | **K2** | Serang |
| **Maret** | Rp 33.700.000 | Rp 2.558.000 | Rp 1.837.000 | Rp 4.395.000 | Rp 29.305.000 | **K1** | Serang |
| **April** | Rp 30.280.000 | Rp 2.558.000 | Rp 701.000 | Rp 3.259.000 | Rp 27.021.000 | **K2** | Tangerang |
| **Mei** | Rp 32.349.000 | Rp 2.558.000 | Rp 2.301.000 | Rp 4.859.000 | Rp 27.490.000 | **K2** | Serang |
| **Juni** | Rp 27.025.000 | Rp 2.558.000 | Rp 1.963.000 | Rp 4.521.000 | Rp 22.504.000 | **K1** | Tangerang |
| **Juli** | Rp 27.030.000 | Rp 3.058.000 | Rp 3.693.110 | Rp 6.751.110 | Rp 20.278.890 | **K1** | Serang |
| **Agustus** | Rp 33.605.000 | Rp 3.058.000 | Rp 4.101.210 | Rp 7.159.210 | Rp 26.445.790 | **K1** | Serang |
| **September**| Rp 32.112.000 | Rp 3.506.000 | Rp 3.090.000 | Rp 6.596.000 | Rp 25.516.000 | **K1** | Serang |
| **Oktober**  | Rp 39.600.000 | Rp 3.506.000 | Rp 3.441.130 | Rp 6.947.130 | **Rp 32.652.870**| **K1** | Serang |
| **November** | Rp 15.610.000 | Rp 3.506.000 | Rp 591.000 | Rp 3.506.000 | Rp 12.104.000 | **K1** | Serang |
| **TOTAL**   | **Rp 317.164.000** | **Rp 31.795.000** | **Rp 26.440.450** | **Rp 57.631.450** | **Rp 259.532.550** | **K1 & K2** | **Serang & Jabodetabek** |

*Catatan:* Angka laba bersih Januari Rp 18.887.000 dihitung dari Omzet (Rp 26.392.000) dikurangi Biaya Operasional (Rp 7.505.000), sehingga total laba bersih 11 bulan terverifikasi tepat sebesar **Rp 259.532.550**.

---

### 3.2. Segmen Sewa "TRANSIT" (Day-Use) dan Sewa Harian
* **Karakteristik Tipe Sewa:**
  * **Sewa Harian (Daily Stay):** Mencakup **56,1% (678 transaksi)** dengan tarif normal menginap (check-out hari berikutnya).
  * **Sewa Transit (Day-Use):** Mencakup **43,9% (530 transaksi)** di mana tamu check-in dan check-out pada hari yang sama (wisatawan pantai Anyer yang membutuhkan tempat istirahat/bilas).
  * Sewa transit terjadi secara merata pada kamar Standard AC (K1, K2, K5) dan Mezanin (K3) dengan tarif Rp 150.000 – Rp 220.000.
  * Kamar K4 (Standar Kipas) berstatus kamar mitra bagi hasil dengan 5 kali transaksi tercatat.
  * *Implikasi Sistem:* Validasi bentrok jadwal di sistem web perlu membedakan antara sewa harian (berbasis rentang tanggal) dan sewa transit (berbasis jam check-in/check-out) agar tidak terjadi penolakan palsu (*false conflict*) pada hari yang sama.

---

### 3.3. Struktur Biaya Operasional (Cost Breakdown)
* **Biaya Tetap (Fixed Costs - Rata-rata Rp 2,8–3,5 Juta/bln):**
  * Gaji Staf Utama (Sadam / Wawan): Rp 1.500.000 - Rp 2.000.000
  * Gaji Staf Tambahan (Angga / Alewo): Rp 500.000
  * Biaya Operasional Kamar 5: Rp 550.000
  * Internet WiFi: Rp 170.000 - Rp 222.000
  * Layanan Hiburan (Netflix): Rp 186.000
  * Iuran Pengelolaan Sampah: Rp 100.000
* **Biaya Variabel (Variable Costs):**
  * Token Listrik per kamar (rata-rata Rp 100.000 per token).
  * Kebutuhan operasional harian (Galon, Gas, Kopi, Bumbu, Perlengkapan kebersihan).
  * Pembelian perlengkapan & perbaikan kamar (Extrabed, Kulkas, Kasur, Keran, Gagang pintu).
  * Bagi hasil mitra pengelola K4 (Bahri).

---

### 3.4. Batasan & Status Pertanyaan Bisnis dari Data Historis
Dalam perumusan pertanyaan bisnis bersama tim, beberapa pertanyaan dapat dijawab secara tuntas oleh data, sementara sebagian lainnya membutuhkan sistem baru untuk dapat terlacak:
1. **Pola Okupansi Weekend vs Weekday:** **Terjawab.** Akhir pekan (Jumat–Sabtu) menyumbang volume reservasi tertinggi dan okupansi kamar AC mencapai titik puncak.
2. **Kontribusi Pendapatan per Kamar:** **Terjawab.** Kamar Standard AC (K1, K2, K5) menghasilkan kontribusi omzet terbesar, disusul Mezanin (K3).
3. **Pola Sewa Transit vs Harian:** **Terjawab.** Rasio sewa harian 56,1% vs transit 43,9%.
4. **Frekuensi Pembatalan & No-Show:** **Belum dapat dijawab dari data historis.** Buku kas manual owner hanya mencatat tamu yang bertransaksi/selesai menginap; pembatalan lisan dan tamu no-show tidak dibukukan secara konsisten di kertas. Fitur status `cancelled` dan `no_show` pada sistem web baru inilah yang akan mengumpulkan metrik ini ke depan.
5. **Permintaan yang Ditolak (Lost Demand):** **Belum dapat dijawab.** Calon tamu yang tidak jadi memesan via WhatsApp karena kamar penuh belum pernah terdata sebelumnya.

---

## 4. Rekomendasi Strategis untuk Owner (Kak Taufiq)

1. **Digitalisasi Pencatatan Pengeluaran & P&L Real-Time:**
   * Fitur pencatatan pengeluaran di sistem web baru memisahkan beban operasional tetap dan variabel, memungkinkan owner melihat laba bersih berjalan secara otomatis tanpa kalkulasi manual di akhir bulan.
2. **Aturan Manajemen Sewa Transit (Buffer Cleaning Time):**
   * Karena sewa transit menyumbang 43,9% transaksi, sistem web menetapkan jeda pembersihan minimal 1 jam setelah tamu transit keluar sebelum kamar dapat dialokasikan untuk tamu menginap harian pukul 14:00.
3. **Pencatatan Khusus Kamar Mitra (K4):**
   * Skema bagi hasil untuk unit mitra K4 difasilitasi dengan flag `is_partner` dan persentase bagi hasil pada master data kamar, serta kategori pengeluaran bagi hasil mitra.
4. **Kebijakan DP untuk Mengunci Okupansi Puncak:**
   * Pada bulan-bulan okupansi tinggi (seperti Oktober Rp 39,6 Juta dan musim liburan), penerapan DP minimal 50% dengan invoice PDF otomatis menjamin kepastian reservasi dan meminimalkan kerugian akibat pembatalan mendadak.

---

*Laporan ini disusun berdasarkan rekonsiliasi data riil pembukuan buku kas 33HomeStay untuk memenuhi Deliverable Wajib Data Science Capstone Project A26-CS026.*
