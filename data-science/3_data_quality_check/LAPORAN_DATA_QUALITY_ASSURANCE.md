# Laporan Pemeriksaan Ketepatan Data (Data Quality Assurance Report)

* **Nama Proyek:** Centralized Reservation System 33HomeStay Anyer
* **Kode Proyek:** A26-CS026
* **Tim Pemeriksa (Data Science):**
  1. **Dewa Bagus Pradnyana Cahyadi Putra (DS014B6Y0434)** — *Lead Data Quality Engineer*
  2. **Dzakwan Muzakki (DS014B6Y0494)** — *Data Science Collaborator*
* **Kategori Deliverable:** Wajib (Pemeriksaan Ketepatan Data / Data Quality Check)
* **Dataset yang Diuji:** `clean_reservations.csv` & `synthetic_reservations.csv` (1.214 baris sintetis terkalibrasi pola aktual, 6 unit kamar aktif termasuk `TH1`)
* **Status Hasil Uji:** **100.0% LOLOS AUDIT (8/8 Uji Lolos)**

---

## 1. Ringkasan Pipeline Dataset Sintetis
Untuk menjaga privasi tamu asli sekaligus menyediakan dataset yang 100% lengkap dan siap pakai untuk EDA maupun operasional sistem website, data dibangkitkan oleh `generate_synthetic_dataset.py` berdasarkan parameter pola aktual buku kas owner (Januari–November 2026):
1. **Tanpa Data Kosong & Tanpa `Unassigned`:** Seluruh 1.214 transaksi memiliki `room_code` valid (`K1`, `K2`, `K3`, `K4`, `K5`, `TH1`), `guest_city`, `channel` (`WhatsApp`, `Walk-In`, `OTA`), `payment_method`, dan `guest_count`.
2. **Kalibrasi Finansial 100% Akurat:** Total omzet per bulan (Januari–November 2026) tepat sama dengan `owner_monthly_pnl_clean.csv` (**Rp 317.164.000**, selisih **Rp 0** di setiap bulan).
3. **Privasi Terjamin:** Nama dan nomor telepon tamu sepenuhnya sintetis.

---

## 2. Matriks Hasil Pemeriksaan Data (`data_quality_check.py`)

| No | Dimensi | Pengujian | Hasil Aktual | Status |
|:---:|:---|:---|:---|:---:|
| 1 | **Completeness** | Tidak ada nilai `NaN` atau placeholder `"-"` | `NaN: 0`, `"-": 0` | **PASS [OK]** |
| 2 | **Uniqueness** | Kode reservasi unik 100% | `0` duplikat | **PASS [OK]** |
| 3 | **Validity** | Format tanggal ISO-8601 (`YYYY-MM-DD`) | 100% valid | **PASS [OK]** |
| 4 | **Validity** | `guest_count` antara `1` s.d. `room_capacity` | 100% valid | **PASS [OK]** |
| 5 | **Consistency** | Kronologi `check_out >= check_in`, `nights`, `rental_type`, `total_price > 0` | 100% konsisten | **PASS [OK]** |
| 6 | **Integrity** | Seluruh kamar terpetakan ke `K1`–`K5` & `TH1`, `0 Unassigned` | 6 unit aktif, `0 Unassigned` | **PASS [OK]** |
| 7 | **Completeness** | Saluran masuk lengkap (`WhatsApp`, `Walk-In`, `OTA`) | `0` label `"Tidak tercatat"` | **PASS [OK]** |
| 8 | **Reconciliation** | Omzet bulanan & total cocok 100% dengan PnL owner | Total Rp 317.164.000, max selisih bulanan Rp 0 | **PASS [OK]** |
