# Data Science Deliverables - 33HomeStay Centralized Reservation System

* **Program:** Capstone Project Asah 2026 (Dicoding & GoTo)
* **Kode Tim:** A26-CS026
* **Tim Data Science:**
  1. **Dewa Bagus Pradnyana Cahyadi Putra (Data Science - DS014B6Y0434)** — *Lead Data Pipeline & Validation Engineer*
  2. **Dzakwan Muzakki (Data Science - DS014B6Y0494)** — *Data Science Collaborator*
* **Mitra UMKM:** 33HomeStay (Bandulu, Anyer, Kabupaten Serang, Banten)
* **Sumber Data Kebenaran (Ground Truth):** Rekapitulasi Buku Tamu Fisik & Kasir 11 Bulan (`Data_tamu.pdf`, Januari – November 2026)

---

## 📂 Struktur Direktori & Artefak

```text
data-science/
├── 0_analisis_peran_data/                    # [WAJIB] Landasan Teoretis & Analisis Peran Data
│   └── readme.md                             # Laporan analisis peran data & perumusan pertanyaan bisnis v2
│
├── 1_data_cleaning/                          # [WAJIB] Pengolahan & Pembersihan Data Riil UMKM
│   ├── extract_actual_all_months.py          # Pipeline ekstraksi rekonsiliasi PDF 11 bulan (Tugas 1-8)
│   ├── impute_missing_values.py              # Skrip verifikasi aturan imputasi K-1, K-2, dan N-1
│   ├── raw_reservations_manual.csv           # Rekap mentah terstandardisasi (1.214 baris, nama disensor)
│   ├── clean_reservations.csv                # Dataset lengkap terverifikasi (1.214 baris: 982 Inti + 232 TH1)
│   ├── clean_reservations_core.csv           # Dataset khusus unit inti operasional sistem (982 baris)
│   ├── date_correction_log.csv               # Log kronologis pembersihan tanggal aturan R1-R7 (34 entri)
│   ├── duplicates_removed.csv                # Log baris duplikat persis yang dibuang (3 baris April)
│   ├── reconciliation_report.csv             # Laporan rekonsiliasi bulanan & tabel terhadap buku owner
│   ├── owner_monthly_pnl_clean.csv           # Rekap laba rugi bulanan bersih (Jan–Nov 2026)
│   └── data_dictionary.md                    # Kamus data skema reservasi, batasan, & tipe sewa
│
├── 2_business_insights/                      # [WAJIB] Temuan Analisis & Rekomendasi Bisnis
│   ├── eda_analysis.py                       # Skrip Exploratory Data Analysis & visualisasi unit inti & TH1
│   ├── plot_actual_pnl.py                    # Visualisasi tren omzet, biaya, dan profit bersih 11 bulan
│   ├── charts/                               # Visualisasi grafik resolusi tinggi:
│   │   ├── 01_occupancy_weekend_vs_weekday.png   # Pola okupansi akhir pekan vs hari kerja unit inti
│   │   ├── 02_revenue_by_room_type.png           # Kontribusi omzet per kamar unit inti & callout TH1
│   │   ├── 03_booking_channel_distribution.png   # Pangsa saluran pemesanan & rincian platform OTA
│   │   ├── 04_origin_city_demographics.png       # Demografi kota asal pelanggan (recorded vs mode)
│   │   └── 05_actual_monthly_pnl_trend.png       # Tren P&L bulanan aktual buku kas owner
│   └── LAPORAN_TEMUAN_DAN_REKOMENDASI_BISNIS.md  # Laporan eksekutif rekomendasi untuk Owner
│
├── 3_data_quality_check/                     # [WAJIB] Audit Ketepatan Data (Data Quality Check)
│   ├── data_quality_check.py                 # Skrip pengujian 8 aspek integritas & collision audit
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
# 1. Menjalankan Ekstraksi, Pembersihan Tanggal, & Rekonsiliasi PDF (Tugas 1-8)
python data-science/1_data_cleaning/extract_actual_all_months.py

# 2. Verifikasi Integritas Imputasi (K-1, K-2, N-1)
python data-science/1_data_cleaning/impute_missing_values.py

# 3. Menjalankan Analisis EDA dan Regenerasi Grafik Bisnis
python data-science/2_business_insights/eda_analysis.py

# 4. Menjalankan Audit Kualitas Data (DQA Suite & Collision Check)
python data-science/3_data_quality_check/data_quality_check.py
```

---

## 🏆 Ringkasan Hasil Capaian & Rekonsiliasi Data Riil

| Dimensi Metrik | Hasil Rekonsiliasi Aktual | Keterangan & Catatan Validasi |
|:---|:---:|:---|
| **Total Baris Unik PDF** | **1.207 baris** | Berhasil memisahkan 407 baris belanja/biaya dan membuang 3 baris duplikat persis. |
| **Total Baris Dataset Analisis** | **1.214 baris** | 1.207 baris unik + 7 baris hasil pemecahan reservasi multi-kamar secara adil. |
| **Unit Inti 33HomeStay (MVP)** | **982 baris** | K1 (257), K2 (251), K5 (205), K3 (154), Unassigned (108), K4 (7). Omzet: **Rp 273.364.000**. |
| **Teras House (TH1 - Scope Luar)** | **232 baris** | Tabel kanan N-1. Omzet: **Rp 43.830.000** (Rata-rata Rp 188.922 / transaksi). |
| **Total Omzet Riil Seluruh Unit** | **Rp 317.194.000** | Selaras sempurna dengan rekap owner (Rp 317.164.000) dengan 2 selisih sah terdokumentasi. |
| **Total Beban Operasional** | **Rp 57.631.450** | Biaya Tetap (Rp 31,79 Jt) + Biaya Variabel (Rp 26,44 Jt). Operating Expense Ratio: 18,17%. |
| **Total Laba Bersih Riil** | **Rp 259.562.550** | Laba bersih operasional riil berjalan selama 11 bulan. |
| **Komposisi Sewa Unit Inti** | **58,9% Daily / 41,1% Transit** | 578 sewa harian (menginap $\ge 1$ malam) dan 404 sewa transit (*day-use* 0 malam). |
| **Pembersihan Kolom Karangan** | **100% Bersih** | Tidak ada `guest_count` palsu, tidak ada `payment_method`/DP karangan, tidak ada kanal `Direct`. |
| **Kepatuhan Privasi Data (PII)** | **100% Tersensor** | Nomor HP asli tidak ada yang bocor (`08XXXXXXXXXX`), nama tamu disamarkan (`Tamu-XXXX`). |
| **Skor Audit DQA** | **100% Lolos (9 dari 9)** | Mengonfirmasi secara empiris **28 kasus double booking** pada unit tercatat fisik. |
