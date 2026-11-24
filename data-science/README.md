# Data Science Deliverables - 33HomeStay Centralized Reservation System

* **Program:** Capstone Project Asah 2026 (Dicoding Indonesia)
* **Kode Tim:** A26-CS026
* **Penanggung Jawab Utama:** **Dzakwan Muzakki (Data Science - DS014B6Y0494)**
* **Kolaborator:** **Dewa Bagus Pradnyaan Cahyadi Putra (Data Science - DS014B6Y0434)**
* **Mitra UMKM:** 33HomeStay (Bandulu, Anyer, Kabupaten Serang, Banten)

---

## 📂 Struktur Direktori & Artefak

```text
data-science/
├── 1_data_cleaning/                          # [WAJIB] Pengolahan & Pembersihan Data Riil UMKM
│   ├── extract_actual_all_months.py          # Ekstraktor ToUnicode PDF seluruh 11 bulan (1.578 transaksi riil)
│   ├── raw_reservations_manual.csv           # Rekap mentah buku tamu aktual 11 bulan (1.578 catatan)
│   ├── clean_dummy_data.py                   # Skrip otomatis data cleaning & transformasi ISO
│   ├── clean_reservations.csv                # Dataset bersih terstandardisasi (1.578 transaksi, Rp 345,4jt)
│   ├── parse_actual_ledger.py                # Agregasi P&L bulanan buku kas owner
│   ├── owner_monthly_pnl_clean.csv           # Rekap laba rugi bulanan bersih (Jan - Nov 2026)
│   └── data_dictionary.md                    # Kamus data lengkap skema reservasi & tipe sewa
│
├── 2_business_insights/                      # [WAJIB] Temuan Analisis & Rekomendasi Bisnis
│   ├── eda_analysis.py                       # Skrip Exploratory Data Analysis & visualisasi 1.578 data
│   ├── plot_actual_pnl.py                    # Visualisasi tren omzet, biaya, dan profit bersih 11 bulan
│   ├── charts/                               # Visualisasi grafik resolusi tinggi:
│   │   ├── 01_occupancy_weekend_vs_weekday.png   # Pola okupansi akhir pekan vs hari kerja
│   │   ├── 02_revenue_by_room_type.png           # Kontribusi omzet per tipe kamar aktual (K1-K5, TH-1)
│   │   ├── 03_booking_channel_distribution.png   # Pangsa saluran pemesanan (WA, OTA, Walk-in)
│   │   ├── 04_origin_city_demographics.png       # Demografi kota asal pelanggan
│   │   └── 05_actual_monthly_pnl_trend.png       # Tren P&L bulanan aktual buku kas owner
│   └── LAPORAN_TEMUAN_DAN_REKOMENDASI_BISNIS.md  # Laporan eksekutif rekomendasi untuk Owner
│
├── 3_data_quality_check/                     # [WAJIB] Audit Ketepatan Data (Data Quality Check)
│   ├── data_quality_check.py                 # Skrip pengujian 6 dimensi integritas data (12 uji)
│   └── LAPORAN_DATA_QUALITY_ASSURANCE.md     # Berita acara uji presisi 100% lolos audit
│
└── 4_ai_dataset_intent/                      # [TAMBAHAN/OPSIONAL] Dataset Intent AI Chatbot
    ├── intent_dataset_full.csv               # Dataset pesan WhatsApp 5 kelas (120 baris)
    ├── train.csv                             # Split latih (70%)
    ├── val.csv                               # Split validasi (15%)
    ├── test.csv                              # Split uji (15%)
    ├── generate_intent_dataset.py            # Skrip generator dataset & stratified split
    ├── annotation_guidelines.md              # Pedoman tertulis aturan pelabelan intent
    ├── calculate_cohens_kappa.py             # Skrip evaluasi kesepakatan antar-anotator
    └── cohens_kappa_report.md                # Laporan Cohen's Kappa (Skor: 0.944 - Almost Perfect)
```

---

## ⚡ Cara Menjalankan Seluruh Modul (Quick Start)

Pastikan Python 3.x dan pustaka dasar (`pandas`, `matplotlib`, `seaborn`, `scikit-learn`) telah terpasang:

```bash
# 1. Menjalankan Pembersihan Data Dummy
python data-science/1_data_cleaning/clean_dummy_data.py

# 2. Menjalankan Analisis EDA dan Pembuatan Grafik Bisnis
python data-science/2_business_insights/eda_analysis.py

# 3. Menjalankan Audit Kualitas Data (11 Uji Integritas)
python data-science/3_data_quality_check/data_quality_check.py

# 4. Membagi Dataset Intent AI (Stratified Split)
python data-science/4_ai_dataset_intent/generate_intent_dataset.py

# 5. Menghitung Skor Inter-Annotator Agreement (Cohen's Kappa)
python data-science/4_ai_dataset_intent/calculate_cohens_kappa.py
```

---

## 🏆 Ringkasan Capaian Jobdesk

| Deliverable | Kategori | PIC | Status | Indikator Keberhasilan |
|:---|:---:|:---:|:---:|:---|
| **Pembersihan Data Dummy** | Wajib | Dzakwan Muzakki | Selesai | 176 baris bersih, format tanggal ISO, nomor HP valid, Kamus Data lengkap |
| **Temuan & Rekomendasi Bisnis** | Wajib | Dzakwan Muzakki | Selesai | 4 grafik visualisasi + Laporan Eksekutif Strategi Dinamis & DP untuk Owner |
| **Pemeriksaan Ketepatan Data** | Wajib | Dzakwan & Dewa | Selesai | Skor Audit DQA: **100% Lolos Uji** (0 Nilai Kosong, 0 Duplikasi, 0 Bentrok Double-Booking) |
| **Dataset Intent AI & Cohen's Kappa** | Tambahan | Dzakwan Muzakki | Selesai | 225 pesan 5 kelas seimbang, Train/Val/Test Split, **Cohen's $\kappa = 0.9245$** (*Almost Perfect Agreement*) |
