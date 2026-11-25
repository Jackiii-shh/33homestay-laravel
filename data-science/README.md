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
│   ├── extract_actual_all_months.py          # Ekstraktor rekonsiliasi PDF 11 bulan (1.208 transaksi riil)
│   ├── raw_reservations_manual.csv           # Rekap hasil ekstraksi buku kas aktual (1.208 data)
│   ├── clean_reservations.csv                # Dataset bersih terverifikasi (1.208 data, nama disensor)
│   ├── parse_actual_ledger.py                # Agregasi P&L bulanan buku kas owner
│   ├── owner_monthly_pnl_clean.csv           # Rekap laba rugi bulanan bersih (Jan–Nov 2026)
│   └── data_dictionary.md                    # Kamus data skema reservasi & tipe sewa
│
├── 2_business_insights/                      # [WAJIB] Temuan Analisis & Rekomendasi Bisnis
│   ├── eda_analysis.py                       # Skrip Exploratory Data Analysis & visualisasi 1.208 data
│   ├── plot_actual_pnl.py                    # Visualisasi tren omzet, biaya, dan profit bersih 11 bulan
│   ├── charts/                               # Visualisasi grafik resolusi tinggi:
│   │   ├── 01_occupancy_weekend_vs_weekday.png   # Pola okupansi akhir pekan vs hari kerja
│   │   ├── 02_revenue_by_room_type.png           # Kontribusi omzet per tipe kamar aktual (K1–K5)
│   │   ├── 03_booking_channel_distribution.png   # Pangsa saluran pemesanan (Direct vs OTA)
│   │   ├── 04_origin_city_demographics.png       # Demografi kota asal pelanggan
│   │   └── 05_actual_monthly_pnl_trend.png       # Tren P&L bulanan aktual buku kas owner
│   └── LAPORAN_TEMUAN_DAN_REKOMENDASI_BISNIS.md  # Laporan eksekutif rekomendasi untuk Owner
│
├── 3_data_quality_check/                     # [WAJIB] Audit Ketepatan Data (Data Quality Check)
│   ├── data_quality_check.py                 # Skrip pengujian 6 dimensi integritas data (11 uji)
│   └── LAPORAN_DATA_QUALITY_ASSURANCE.md     # Berita acara audit kualitas data (100% Lolos)
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
# 1. Menjalankan Ekstraksi & Rekonsiliasi Data Aktual dari PDF Buku Kas
python data-science/1_data_cleaning/extract_actual_all_months.py

# 2. Menjalankan Agregasi Laba Rugi (P&L) Buku Kas Bulanan Owner
python data-science/1_data_cleaning/parse_actual_ledger.py

# 3. Menjalankan Analisis EDA dan Pembuatan Grafik Bisnis
python data-science/2_business_insights/eda_analysis.py

# 4. Menjalankan Audit Kualitas Data (11 Uji Integritas)
python data-science/3_data_quality_check/data_quality_check.py

# 5. Membagi Dataset Intent AI (Stratified Split)
python data-science/4_ai_dataset_intent/generate_intent_dataset.py

# 6. Menghitung Skor Inter-Annotator Agreement (Cohen's Kappa)
python data-science/4_ai_dataset_intent/calculate_cohens_kappa.py
```

---

## 🏆 Ringkasan Capaian Jobdesk

| Deliverable | Kategori | PIC | Status | Indikator Keberhasilan |
|:---|:---:|:---:|:---:|:---|
| **Pembersihan Data Riil** | Wajib | Dzakwan Muzakki | Selesai | **1.208 reservasi aktual** terverifikasi (bebas dari baris belanja operasional), identitas nama tamu disensor/dianonimkan (`Tamu-XXXX`), format tanggal ISO, Kamus Data lengkap |
| **Temuan & Rekomendasi Bisnis** | Wajib | Dzakwan Muzakki | Selesai | 5 grafik visualisasi + Laporan Eksekutif Strategi & Rekomendasi untuk Owner. Angka P&L mengacu buku kas riil owner: Omzet Rp 317,16 jt, Beban Rp 57,63 jt, Laba Bersih Rp 259,53 jt |
| **Pemeriksaan Ketepatan Data** | Wajib | Dzakwan & Dewa | Selesai | Skor Audit DQA: **100% Lolos Uji (11 dari 11 uji)**, terpetakan 32 kasus tumpang tindih tanggal pada buku manual |
| **Dataset Intent AI & Cohen's Kappa** | Tambahan | Dzakwan Muzakki | Selesai | 225 pesan 5 kelas seimbang, Train/Val/Test Split, **Cohen's $\kappa = 0.9245$** (*Almost Perfect Agreement*) |
