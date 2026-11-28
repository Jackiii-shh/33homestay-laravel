# Dokumen Temuan Analisis Data & Rekomendasi Bisnis 33HomeStay
## Berdasarkan Dataset Sintetis Terkalibrasi Pola Buku Kas Aktual (Januari – November 2026)

* **Nama Proyek:** Centralized Reservation System 33HomeStay Anyer
* **Kode Tim:** A26-CS026
* **Penulis:**
  1. **Dewa Bagus Pradnyana Cahyadi Putra (DS014B6Y0434)** — *Lead Business & Data Analyst*
  2. **Dzakwan Muzakki (DS014B6Y0494)** — *Data Science Collaborator*
* **Kategori Deliverable:** Wajib (Penyajian Temuan, Insight, & Rekomendasi Bisnis)
* **Mitra Sasaran:** Kak Taufiq (Owner 33HomeStay, Anyer, Banten)
* **Periode & Sumber Data:** 1.214 Transaksi Sintetis Terkalibrasi Pola Riil (Januari – November 2026, 6 Unit Aktif termasuk `TH1`)

---

## 1. Ringkasan Eksekutif Finansial (Executive Summary)
Dataset sintetis ini dibangun menggunakan [generate_synthetic_dataset.py](file:///c:/Users/Pongo/33homestay-laravel/data-science/1_data_cleaning/generate_synthetic_dataset.py) dengan mengkalibrasi seluruh distribusi bulanan, tarif, pola menginap, dan demografi agar **identik secara statistik dan finansial** dengan buku kas owner tanpa mengekspos data pribadi tamu asli:
* **Total Omzet Terkalibrasi (11 Bulan):** **Rp 317.164.000** (Selisih **Rp 0** di seluruh 11 bulan terhadap rekap `owner_monthly_pnl_clean.csv`).
* **Total Beban Operasional (11 Bulan):** **Rp 57.631.450** (Rata-rata Rp 5,24 Juta / bulan).
* **Total Laba Bersih (Net Profit):** **Rp 259.532.550** (Margin laba bersih: **81,83%**).
* **Cakupan Unit Lengkap (6 Unit Aktif, 0 `Unassigned`):** Seluruh **1.214 transaksi** terpetakan lengkap ke `K1`, `K2`, `K3`, `K4`, `K5`, dan `TH1` (Tera House).

---

## 2. Struktur & Kontribusi Pendapatan per Unit Kamar
Seluruh 6 unit kamar aktif tercatat rapi tanpa kategori `Unassigned`:
1. **K2 (Standard Room AC):** Tarif Rp 300.000/malam | **306 reservasi (Rp 79,71 Juta / 25,1%)**.
2. **K1 (Standard Room AC):** Tarif Rp 300.000/malam | **286 reservasi (Rp 71,23 Juta / 22,5%)**.
3. **K5 (Standard Room AC):** Tarif Rp 300.000/malam | **239 reservasi (Rp 65,09 Juta / 20,5%)**.
4. **TH1 (Tera House):** Unit villa keluarga (kapasitas 6 orang) | **230 reservasi (Rp 51,23 Juta / 16,2%)**.
5. **K3 (Mezanin Bertingkat):** Tarif Rp 350.000/malam (kapasitas 4 orang) | **151 reservasi (Rp 49,56 Juta / 15,6%)**.
6. **K4 (Standard Room Kipas):** Kamar mitra bagi hasil | **2 reservasi (Rp 0,35 Juta / 0,1%)**.

---

## 3. Temuan Utama Analisis Data (Key Findings)

### 3.1. Pola Sewa Harian (Daily) vs Sewa Singgah (Transit / Day-Use)
* **Sewa Harian (Daily Stay):** **687 transaksi (56,6%)** dengan durasi minimal 1 malam.
* **Sewa Transit (Day-Use):** **527 transaksi (43,4%)** dengan check-in dan check-out di hari yang sama (`nights = 0`).
* *Implikasi Sistem Web:* Fitur check-in dan validasi ketersediaan sistem harus mendukung transaksi *day-use* pada hari yang sama (`check_in == check_out == today`) tanpa ditolak sebagai masa inap lewat.

### 3.2. Pola Pemesanan Hari Kerja vs Akhir Pekan
* **Hari Kerja (Senin–Jumat, 5 hari):** Menyumbang **692 transaksi (57,0%)** dengan rata-rata **138,4 transaksi/hari**.
* **Akhir Pekan (Sabtu–Minggu, 2 hari):** Menyumbang **522 transaksi (43,0%)** dengan kepadatan harian hampir dua kali lipat (**261,0 transaksi/hari**).

### 3.3. Saluran Masuk Reservasi (Channels)
Seluruh saluran masuk terisi lengkap (0 `"Tidak tercatat"`):
* **WhatsApp:** **670 transaksi (55,2%)**
* **Walk-In:** **529 transaksi (43,6%)**
* **OTA (Online Travel Agent):** **15 transaksi (1,2%)** — terdiri dari Agoda, Traveloka, dan Tiket.com.

### 3.4. Demografi Kota Asal Tamu
Berdasarkan distribusi seluruh **1.214 transaksi** (0 nilai kosong):
* Wilayah dominan berasal dari **Serang, Cilegon, dan Tangerang**, diikuti oleh **Anyer, Jakarta, Cikande, Pandeglang, Bekasi, Bogor, Rangkasbitung, dan Merak**.

### 3.5. Tren Bulanan & Musiman (PnL)
* Puncak omzet terjadi pada bulan **Oktober (Rp 39,60 Juta)**, **Maret (Rp 33,70 Juta)**, dan **Agustus (Rp 33,61 Juta)**.
* Rasio biaya operasional terhadap omzet adalah **18,17%**, menghasilkan margin laba bersih **81,83%**.

---

## 4. Evaluasi Pertanyaan Bisnis

| Pertanyaan Bisnis | Status | Ringkasan Temuan |
|:---|:---:|:---|
| **1. Berapa kontribusi pendapatan per unit kamar?** | **Terjawab** | Trio Standard AC (`K2`, `K1`, `K5`) menyumbang **68,1%** omzet, diikuti `TH1` (**16,2%**), `K3` (**15,6%**), dan `K4` (**0,1%**). |
| **2. Bagaimana proporsi sewa harian vs sewa transit?** | **Terjawab** | **56,6%** sewa harian (687 trx) vs **43,4%** sewa transit (527 trx). |
| **3. Bagaimana pola okupansi hari kerja vs akhir pekan?** | **Terjawab** | Hari kerja (Senin–Jumat) **57,0%** volume, namun akhir pekan (Sabtu–Minggu) padat **261,0 trx/hari** (~1,89x hari kerja). |
| **4. Bagaimana kesiapan data operasional di website?** | **Siap Pakai** | Seluruh 1.214 baris terimpor ke MySQL tanpa `Unassigned`, tanpa nilai kosong, dan mendukung `TH1` serta transit hari ini. |

---

## 5. Rekomendasi Strategis
1. **Dukungan Penuh Check-In Transit & Validasi Bentrok Real-Time:** Sistem mengizinkan check-in untuk reservasi *day-use* (`check_out == today`) serta mengunci jadwal kamar via transaksi DB (`lockForUpdate`).
2. **Optimasi Harga Akhir Pekan (*Dynamic Pricing*):** Terapkan penyesuaian tarif 15–20% pada Sabtu–Minggu mengingat kepadatan transaksi hampir 2x lipat hari kerja.
3. **Pemantauan Kinerja 6 Unit Terpusat (termasuk Tera House `TH1`):** Laporan pembayaran, okupansi, dan pengeluaran kini memantau seluruh 6 unit secara utuh.
