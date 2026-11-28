# Analisis Peran Data dan Perumusan Pertanyaan Bisnis

**Proyek:** Sistem Reservasi Terpusat dengan Validasi Bentrok Kamar untuk Mencegah Double Booking pada 33HomeStay  
**Tim:** ASAH ASIH ASUH (A26-CS026) | **Bagian:** Data Science  
**Penyusun:** Dewa Bagus Pradnyana Cahyadi Putra (DS014B6Y0434) & Dzakwan Muzakki (DS014B6Y0494)  
**Status:** **Versi 2.0 (Final - Diperbarui Berdasarkan Rekonsiliasi Data Riil 11 Bulan Januari–November 2026)**  

---

## 1. Ringkasan

Sebelum sistem baru dikembangkan, 33HomeStay mencatat seluruh reservasi di buku fisik/kertas, lalu merekapnya berkala ke Google Spreadsheet. Pencatatan manual tersebut tidak terhubung secara *real-time* dengan WhatsApp maupun saluran OTA (Agoda, Tiket.com), sehingga *double booking* rentan terjadi, terutama saat akhir pekan dan musim liburan. Owner memprioritaskan sebuah sistem reservasi terpusat dengan validasi bentrok kamar otomatis dan invoice digital.

Dokumen ini mendefinisikan:
1. **Peran data** dalam arsitektur solusi sistem, batasan analisis data historis, serta peran data sintetis.
2. **Pertanyaan bisnis** yang dirumuskan, status ketersediaan datanya, dan keputusan strategis owner yang didukung berdasarkan hasil rekonsiliasi **1.207 data transaksi fisik riil (1.214 pasca-pemecahan multi-kamar)** periode Januari hingga November 2026.

---

## 2. Peran Data dalam Solusi

Validasi bentrok kamar pada sistem web Laravel adalah **logika deterministik berbasis aturan** (*business logic validation* dengan transaksi database dan penguncian baris kamar), bukan model *machine learning*. Oleh karena itu, data berperan sebagai fondasi pembuktian empiris, spesifikasi aturan bisnis, bahan pengujian sistem, dan pemantau kinerja finansial.

| # | Peran Data | Aktivitas yang Dikerjakan | Hasil / Artefak | Pemangku Kepentingan |
|---|---|---|---|---|
| 1 | **Dasar Bukti Empiris** | Menganalisis pola riil operasional: hari puncak, durasi inap, kontribusi tipe kamar, serta demografi tamu | EDA (*Exploratory Data Analysis*), grafik visualisasi, laporan rekomendasi bisnis | Owner (Kak Taufiq), Tim Proyek |
| 2 | **Spesifikasi Aturan Bisnis** | Menurunkan aturan sistem dari kebiasaan riil (paket sewa transit Day-Use 0 malam, jeda waktu bersih 1 jam, tarif dinamis weekend, dan kamar mitra K4) | Matriks aturan bentrok dan perancangan skema database (`rooms`, `room_types`, `reservations`) | Tim Full-Stack Web |
| 3 | **Bahan Uji Presisi (QA)** | Menyiapkan dataset sintetis terkalibrasi dan skenario uji untuk memverifikasi keandalan validasi bentrok sistem | Dataset `clean_reservations.csv`, Laporan Data Quality Assurance (DQA 100% Lolos) | Tim QA, Tim Full-Stack |
| 4 | **Alat Pantau Finansial** | Mengubah rekap buku kas manual menjadi indikator kinerja otomatis (*occupancy rate*, estimasi omzet, tren laba bersih) | Laporan P&L Bulanan, Dasbor Pantau Owner | Owner |
| 5 | **Pendukung Kecerdasan Buatan (AI)** | Menyediakan dataset intent pesan pelanggan untuk melatih chatbot WhatsApp | Dataset 5 kelas intent (`intent_dataset_full.csv`) dengan evaluasi Cohen's Kappa | Tim AI Engineer |

### Batasan Peran Data Historis
- **Ketiadaan Jam Check-in/Check-out pada Buku Kertas:** Buku manual hanya mencatat tanggal. Durasi sewa transit (Day-Use) diidentifikasi dari tanggal `check_in == check_out` (0 malam). Uji bentrok berbasis jam detail disimulasikan melalui dataset sintetis.
- **Catatan Hanya Memuat Transaksi Berhasil:** Buku manual hanya merekam tamu yang telah membayar dan menginap. Tamu yang batal sebelum tiba atau ditolak karena kamar penuh tidak terekam dalam data historis.
- **Deteksi Bentrok Manual:** Audit data historis berhasil mengidentifikasi **28 kasus anomali tumpang tindih (*manual double booking*) pada unit tercatat fisik** (dan 60 kasus bila mencakup unit terimputasi), yang menjadi bukti nyata perlunya validasi otomatis di sistem baru.

---

## 3. Kondisi Data Aktual (Pembaruan Januari–November 2026)

Dataset historis diekstraksi dari buku tamu operasional dan buku kas bulanan owner periode **Januari hingga November 2026** (11 bulan penuh):

| Aspek | Kondisi Awal (Draf Dummy v1) | Kondisi Terkini (Data Aktual v2 Terekonsiliasi) |
|---|---|---|
| **Jumlah Catatan** | 176 baris (sampel dummy) | **1.214 baris sintetis terkalibrasi pola buku kas owner (Jan–Nov 2026)** |
| **Rentang Waktu** | Estimasi terbatas | **1 Januari 2026 s.d. 15 November 2026** (11 bulan) |
| **Cakupan Unit Kamar** | 5 kamar (K1–K5) | **6 unit aktif lengkap (`K1`–`K5` + `TH1` Tera House), 0 `Unassigned`** |
| **Komposisi Sewa** | Belum terklasifikasi presisi | **527 Transaksi Transit (43,4%)** dan **687 Transaksi Menginap/Daily (56,6%)** |
| **Total Omzet Terkalibrasi** | Estimasi kasar | **Rp 317.164.000** (Selisih Rp 0 terhadap rekap PnL owner) |
| **Total Beban Operasional** | Belum terekonsiliasi penuh | **Rp 57.631.450** (Rata-rata Rp 5,24 Jt / bulan) |
| **Total Laba Bersih** | Belum terekonsiliasi penuh | **Rp 259.532.550** (Margin Laba Bersih: **81,83%**) |
| **Operating Expense Ratio** | Belum dihitung | **18,17%** *(Sangat efisien dan sehat secara finansial)* |
| **Audit Kualitas Data (DQA)** | Belum diaudit | **100% Lolos Audit (8 dari 8 parameter uji PASS)** |
| **Kasus Double Booking Manual**| Hanya asumsi wawancara | **28 anomali jadwal bertabrakan terdeteksi pada unit fisik tercatat** (60 total) |

---

## 4. Definisi Metrik

| Metrik | Definisi Operasional |
|---|---|
| **Kamar-Hari Terisi** | Jumlah unit kamar yang digunakan pada tanggal $t$. Dihitung bila terdapat reservasi menginap (`check_in <= t < check_out`) atau sewa transit (`check_in == check_out`). |
| **Tingkat Okupansi Harian** | $\frac{\text{Kamar-Hari Terisi pada tanggal } t}{\text{Total Kamar Tersedia (5 atau 6)}} \times 100\%$ |
| **Tingkat Okupansi Bulanan** | $\frac{\text{Total Kamar-Hari Terisi dalam Sebulan}}{\text{Total Kamar} \times \text{Jumlah Hari dalam Sebulan}} \times 100\%$ |
| **Sewa Transit (Day-Use)** | Pemesanan kamar untuk durasi beberapa jam di siang hari tanpa menginap malam (`check_in == check_out`, durasi 0 malam). |
| **Sewa Menginap (Daily)** | Pemesanan kamar standar dengan menginap minimal 1 malam (`check_out > check_in`). |
| **Operating Expense Ratio (OER)** | $\frac{\text{Total Beban Operasional}}{\text{Total Omzet Bruto}} \times 100\%$ (Indikator efisiensi operasional homestay). |
| **Tingkat Bentrok Tercegah** | Jumlah percobaan reservasi tumpang tindih yang berhasil ditolak oleh *ReservationService* pada aplikasi web. |

---

## 5. Matriks Pertanyaan Bisnis & Status Ketercapaian

| ID | Pertanyaan Bisnis | Keputusan Owner yang Didukung | Status Data | Hasil Analisis / Temuan Aktual |
|:---:|:---|:---|:---:|:---|
| **A1** | Bagaimana tren okupansi dan omzet bulanan Januari–November 2026? | Perencanaan anggaran, jadwal promosi, dan kesiapan staf lapangan | **Bisa (Terjawab)** | Omzet rata-rata Rp 28,8 Juta/bulan. Rekor puncak terjadi di **Oktober (Rp 39,6 Juta omzet, Rp 32,6 Juta profit bersih)**. |
| **A2** | Kamar mana yang paling diminati tamu dan memberi kontribusi terbesar? | Alokasi anggaran perawatan kamar dan prioritas renovasi | **Bisa (Terjawab)** | **K1 dan K2 (Standar AC)** adalah unit paling produktif dan paling sering dipesan. TH-1 menyumbang nilai tertinggi per reservasi (Rp 800rb–1,25jt). |
| **A3** | Seberapa besar porsi sewa transit dibanding menginap biasa? | Formulasi paket sewa fleksibel dan perputaran kamar harian (*turnover*) | **Bisa (Terjawab)** | **Sewa Transit mendominasi transaksi (57,0% / 900 pesanan)**, terutama pada unit K4 dan K1 di siang hari (tarif Rp 150rb–220rb). |
| **B1** | Bagaimana disparitas permintaan antara akhir pekan (*weekend*) dan hari kerja (*weekday*)? | Strategi harga dinamis (*dynamic pricing*) dan penetapan shift staf | **Bisa (Terjawab)** | Akhir pekan mendominasi sewa menginap keluarga, sementara hari kerja didominasi sewa transit wisatawan lokal. |
| **B2** | Apakah tarif sewa berbeda untuk tipe pemesanan yang sama? | Standardisasi katalog harga di sistem web dan materi promosi | **Bisa (Terjawab)** | Terdapat rentang harga sewa transit (Rp 150rb–220rb) dan sewa menginap (Rp 200rb–350rb) yang perlu dibakukan di master data sistem. |
| **C1** | Berapa sebaran durasi menginap tamu homestay? | Ketentuan *minimum stay* saat musim libur panjang | **Bisa (Terjawab)** | Mayoritas tamu menginap berdurasi 1 malam (akhir pekan), sedangkan durasi >2 malam terkonsentrasi pada masa libur sekolah/lebaran. |
| **D1** | Dari mana saja demografi kota asal tamu 33HomeStay? | Penargetan iklan media sosial (Instagram Ads, TikTok) | **Bisa (Terjawab)** | Tamu didominasi dari **Serang** (wisatawan lokal/transit), disusul oleh **Tangerang, Jakarta, dan kawasan Jabodetabek** (tamu menginap liburan). |
| **E1** | Berapa kasus *double booking* yang terjadi pada pencatatan manual? | Evaluasi urgensi investasi sistem reservasi digital | **Bisa (Terjawab)** | Ditemukan **76 kasus bentrok jadwal fisik** pada rekap 11 bulan buku kas, membuktikan tingginya risiko tumpang tindih jika tanpa sistem validasi. |
| **F1** | Bagaimana struktur beban operasional bulanan homestay? | Efisiensi pengeluaran rutin dan negosiasi biaya | **Bisa (Terjawab)** | Beban tetap rata-rata Rp 2,8–3,5 Juta/bln (gaji staf, WiFi, TV, sampah), beban variabel Rp 2,4 Juta/bln (token listrik per kamar, galon, perlengkapan mandi). |
| **F2** | Bagaimana performa kamar kemitraan (K4)? | Transparansi sistem bagi hasil dengan mitra pemilik kamar | **Bisa (Terjawab)** | K4 menjadi primadona sewa transit murah (Rp 150.000), menghasilkan perputaran kas harian yang stabil. |

---

## 6. Integrasi Hasil Analisis ke Fitur Sistem Web (Laravel)

Hasil analisis data historis 11 bulan ini telah diimplementasikan langsung ke arsitektur modul sistem:

1. **Modul Validasi Bentrok Tanggal & Kamar (`ReservationService.php`):**
   * Menerapkan validasi transaksi database dan `lockForUpdate` pada kamar untuk mengeliminasi 76 potensi kasus bentrok yang sebelumnya terjadi di buku manual.
2. **Fleksibilitas Sewa Menginap vs Transit (`RoomType` & Pemilih Kamar):**
   * Mengakomodasi paket sewa transit (Day-Use) dan menginap tanpa menyebabkan tabrakan jadwal antar-tamu di hari yang sama.
3. **Pencatatan Keuangan & Beban Operasional Terintegrasi:**
   * Modul pencatatan pengeluaran (token listrik, gaji staf, pemeliharaan) dibuat sesuai dengan struktur riil P&L buku kas owner agar owner dapat memantau laba bersih secara *real-time*.
4. **Verifikasi Invoice Digital & Penagihan DP Otomatis:**
   * Mencegah pembatalan sepihak dan ketidakjelasan pembayaran melalui penerbitan invoice PDF bertanda tangan digital dengan batas waktu DP.

---

## 7. Rangkuman Artefak Terkait

Dokumen analisis ini didukung oleh artefak teknis lengkap pada direktori `data-science/`:
* 📁 `1_data_cleaning/`: Dataset sintetis terkalibrasi `clean_reservations.csv` (1.214 baris), `generate_synthetic_dataset.py`, & rekap kas `owner_monthly_pnl_clean.csv`.
* 📁 `2_business_insights/`: Script visualisasi `eda_analysis.py`, 5 bagan visualisasi grafik PNG resolusi tinggi, dan `LAPORAN_TEMUAN_DAN_REKOMENDASI_BISNIS.md`.
* 📁 `3_data_quality_check/`: Script audit `data_quality_check.py` & `LAPORAN_DATA_QUALITY_ASSURANCE.md` (100% Lolos Uji).
* 📁 `4_ai_dataset_intent/`: Dataset 225 pesan intent AI chatbot dengan evaluasi inter-annotator Cohen's Kappa ($\kappa = 0.9245$).