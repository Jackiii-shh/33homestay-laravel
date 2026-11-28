# Data Science Deliverables - 33HomeStay Centralized Reservation System

* **Program:** Capstone Project Asah 2026 (Dicoding & GoTo)
* **Kode Tim:** A26-CS026
* **Tim Data Science:**
  1. **Dewa Bagus Pradnyana Cahyadi Putra (Data Science - DS014B6Y0434)** — *Lead Data Pipeline & Validation Engineer*
  2. **Dzakwan Muzakki (Data Science - DS014B6Y0494)** — *Data Science Collaborator*
* **Mitra UMKM:** 33HomeStay (Bandulu, Anyer, Kabupaten Serang, Banten)
* **Basis Dataset:** Dataset Sintetis Terkalibrasi Pola Buku Kas Aktual 11 Bulan (`Data_tamu.pdf`, Januari – November 2026)

---

## 📂 Struktur Direktori & Artefak

```text
data-science/
├── 0_analisis_peran_data/                    # [WAJIB] Landasan Teoretis & Analisis Peran Data
│   └── readme.md                             # Laporan analisis peran data & perumusan pertanyaan bisnis
│
├── 1_data_cleaning/                          # [WAJIB] Dataset Sintetis & Kalibrasi Keuangan
│   ├── generate_synthetic_dataset.py         # Generator dataset sintetis terkalibrasi pola aktual (6 unit)
│   ├── synthetic_reservations.csv            # Dataset sintetis lengkap (1.214 baris, 6 unit termasuk TH1)
│   ├── clean_reservations.csv                # Dataset bersih siap analisis & seeder Laravel (1.214 baris)
│   ├── raw_reservations_manual.csv           # Salinan arsip terstandardisasi (1.214 baris)
│   ├── owner_monthly_pnl_clean.csv           # Rekap laba rugi bulanan bersih (Jan–Nov 2026)
│   ├── extract_actual_all_months.py          # Pipeline historis ekstraksi PDF 11 bulan (referensi pola)
│   └── data_dictionary.md                    # Kamus data skema reservasi, batasan, & tipe sewa
│
├── 2_business_insights/                      # [WAJIB] Temuan Analisis & Rekomendasi Bisnis
│   ├── eda_analysis.py                       # Skrip Exploratory Data Analysis & visualisasi 6 unit aktif
│   ├── plot_actual_pnl.py                    # Visualisasi tren omzet, biaya, dan profit bersih 11 bulan
│   ├── charts/                               # Visualisasi grafik resolusi tinggi:
│   │   ├── 01_occupancy_weekend_vs_weekday.png   # Pola pemesanan Hari Kerja (Senin–Jumat) vs Akhir Pekan (Sabtu–Minggu)
│   │   ├── 02_revenue_by_room_type.png           # Kontribusi omzet per unit kamar (K1–K5 + TH1)
│   │   ├── 03_booking_channel_distribution.png   # Pangsa saluran masuk (WhatsApp, Walk-In, OTA) & rincian OTA
│   │   ├── 04_origin_city_demographics.png       # Top 8 demografi kota asal tamu
│   │   └── 05_actual_monthly_pnl_trend.png       # Tren P&L bulanan (omzet, biaya, laba bersih)
│   └── LAPORAN_TEMUAN_DAN_REKOMENDASI_BISNIS.md  # Laporan eksekutif rekomendasi untuk Owner
│
├── 3_data_quality_check/                     # [WAJIB] Audit Ketepatan Data (Data Quality Check)
│   ├── data_quality_check.py                 # Skrip pengujian 8 parameter integritas & rekonsiliasi
│   └── LAPORAN_DATA_QUALITY_ASSURANCE.md     # Berita acara audit kualitas data (100% Lolos Audit)
│
└── 4_ai_dataset_intent/                      # [TAMBAHAN/OPSIONAL] Dataset Intent AI Chatbot
    ├── intent_dataset_full.csv               # Dataset pesan WhatsApp 5 kelas (225 baris)
    ├── train.csv                             # Split latih (70%)
    ├── val.csv                               # Split validasi (15%)
    ├── test.csv                              # Split uji (15%)
    ├── generate_intent_dataset.py            # Skrip generator dataset & stratified split
    ├── annotation_guidelines.md              # Pedoman tertulis aturan pelabelan intent
    ├── calculate_cohens_kappa.py             # Skrip evaluasi kesepakatan antar-anotator
    └── cohens_kappa_report.md                # Laporan Cohen's Kappa (Skor: 0.9245 - Almost Perfect)
```

---

## ⚡ Cara Menjalankan Seluruh Pipeline (Quick Start)

Gunakan lingkungan Python 3.10+ dengan pustaka `pandas`, `numpy`, `matplotlib`:

```bash
# 1. Membangkitkan Dataset Sintetis Terkalibrasi Pola Aktual (1.214 baris, 6 unit termasuk TH1)
python data-science/1_data_cleaning/generate_synthetic_dataset.py

# 2. Menjalankan Analisis EDA dan Regenerasi Grafik Bisnis
python data-science/2_business_insights/eda_analysis.py

# 3. Menjalankan Audit Kualitas Data (DQA Suite & Rekonsiliasi Omzet)
python data-science/3_data_quality_check/data_quality_check.py
```

---

## 🏆 Ringkasan Hasil Capaian & Kalibrasi Dataset

| Dimensi Metrik | Hasil Terkalibrasi | Keterangan & Catatan Validasi |
|:---|:---:|:---|
| **Total Baris Dataset** | **1.214 baris** | Dataset sintetis lengkap berbasis distribusi 11 bulan buku kas (`Jan–Nov 2026`). |
| **Cakupan Unit Kamar (6 Unit)** | **K1, K2, K3, K4, K5, TH1** | `K2` (306), `K1` (286), `K5` (239), `TH1` (230), `K3` (151), `K4` (2). **0 `Unassigned`**. |
| **Total Omzet Terkalibrasi** | **Rp 317.164.000** | Selisih **Rp 0** di setiap bulan terhadap rekap PnL owner (`owner_monthly_pnl_clean.csv`). |
| **Total Beban Operasional** | **Rp 57.631.450** | Biaya Tetap + Biaya Variabel selama 11 bulan. Operating Expense Ratio: **18,17%**. |
| **Total Laba Bersih** | **Rp 259.532.550** | Margin laba bersih operasional sebesar **81,83%**. |
| **Komposisi Sewa** | **56,6% Daily / 43,4% Transit** | 687 sewa harian ($\ge 1$ malam) dan 527 sewa transit (*day-use* 0 malam). |
| **Pola Mingguan** | **57,0% Sen–Jum / 43,0% Sab–Min** | Hari kerja 692 trx (138,4/hari); akhir pekan 522 trx (261,0/hari — hampir 2x lebih padat). |
| **Kelengkapan Kolom** | **100% Terisi** | `0` nilai `NaN`, `0` `"-"`, `0` `Unassigned`, dan `0` `"Tidak tercatat"`. |
| **Kepatuhan Privasi Data (PII)** | **100% Sintetis** | Nama dan nomor telepon tamu (`0812XXXXXXXX`) sepenuhnya sintetis. |
| **Skor Audit DQA** | **100% Lolos (8 dari 8)** | Lolos uji kelengkapan, keunikan, validitas, konsistensi, integritas unit, dan rekonsiliasi omzet. |
