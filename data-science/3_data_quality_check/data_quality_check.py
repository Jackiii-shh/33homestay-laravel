"""
Skrip Pengujian Ketepatan & Kualitas Data (Data Quality Assurance / DQA)
Dataset Sintetis Terkalibrasi Pola Aktual 33HomeStay (Januari - November 2026)
Penanggung Jawab:
- Dzakwan Muzakki (Data Science - DS014B6Y0494)
- Dewa Bagus Pradnyana Cahyadi Putra (Data Science - DS014B6Y0434)
Capstone Project: A26-CS026
"""

import os
import pandas as pd
from datetime import datetime

CURRENT_DIR = os.path.dirname(os.path.abspath(__file__))
DATA_DIR = os.path.join(CURRENT_DIR, "..", "1_data_cleaning")
CLEAN_CSV = os.path.join(DATA_DIR, "clean_reservations.csv")
PNL_CSV = os.path.join(DATA_DIR, "owner_monthly_pnl_clean.csv")

VALID_ROOMS = {"K1", "K2", "K3", "K4", "K5", "TH1"}


class DataQualityChecker:
    def __init__(self, df: pd.DataFrame, df_pnl: pd.DataFrame | None):
        self.df = df
        self.df_pnl = df_pnl
        self.total_checks = 0
        self.passed_checks = 0

    def log(self, dim: str, desc: str, passed: bool, details: str = ""):
        self.total_checks += 1
        if passed:
            self.passed_checks += 1
        status_str = "PASS [OK]" if passed else "FAIL [X]"
        print(f"[{status_str}] {dim} - {desc}")
        if details:
            print(f"        Detail: {details}")

    def run_all(self):
        print("=" * 72)
        print("AUDIT KETEPATAN DATA (DQA) — DATASET SINTETIS 33HOMESTAY (6 UNIT)")
        print(f"Total Baris: {len(self.df)} | Periode: Januari - November 2026")
        print("=" * 72)

        # 1. Kelengkapan: 0 NaN dan 0 '-'
        nan_count = int(self.df.isna().sum().sum())
        dash_count = int(self.df.astype(str).eq("-").sum().sum())
        self.log(
            "Completeness",
            "Tidak ada nilai kosong (NaN) maupun placeholder '-' di seluruh kolom",
            nan_count == 0 and dash_count == 0,
            f"NaN: {nan_count}, '-': {dash_count}",
        )

        # 2. Keunikan kode reservasi
        dup_codes = int(self.df["reservation_code"].duplicated().sum())
        self.log("Uniqueness", "Kode reservasi 100% unik", dup_codes == 0, f"Duplikat: {dup_codes}")

        # 3. Validitas tanggal & kapasitas
        def is_iso(val):
            try:
                datetime.strptime(str(val), "%Y-%m-%d")
                return True
            except ValueError:
                return False

        dates_ok = self.df["check_in"].apply(is_iso).all() and self.df["check_out"].apply(is_iso).all()
        self.log("Validity", "Seluruh tanggal berformat ISO-8601 (YYYY-MM-DD)", bool(dates_ok))

        cap_ok = bool(((self.df["guest_count"] >= 1) & (self.df["guest_count"] <= self.df["room_capacity"])).all())
        self.log("Validity", "Jumlah tamu selalu 1 s.d. kapasitas unit kamar", cap_ok)

        # 4. Konsistensi tanggal, nights, rental_type, dan nominal positif
        dt_in = pd.to_datetime(self.df["check_in"])
        dt_out = pd.to_datetime(self.df["check_out"])
        chrono_ok = bool((dt_out >= dt_in).all())
        nights_ok = bool(((dt_out - dt_in).dt.days == self.df["nights"]).all())
        type_ok = bool((
            ((self.df["rental_type"] == "transit") & (self.df["nights"] == 0)) |
            ((self.df["rental_type"] == "daily") & (self.df["nights"] >= 1))
        ).all())
        price_ok = bool((self.df["total_price"] > 0).all())
        self.log("Consistency", "Kronologi check-in/out, nights, rental_type, dan nominal positif valid", chrono_ok and nights_ok and type_ok and price_ok)

        # 5. Integritas kamar (6 unit aktif termasuk TH1, 0 Unassigned)
        rooms_ok = bool(self.df["room_code"].isin(VALID_ROOMS).all())
        unassigned_n = int((self.df["room_code"] == "Unassigned").sum())
        self.log(
            "Integrity",
            "Seluruh reservasi terpetakan ke 6 unit kamar aktif (K1-K5 & TH1), 0 Unassigned",
            rooms_ok and unassigned_n == 0,
            f"Unassigned: {unassigned_n}, Unit terdeteksi: {sorted(self.df['room_code'].unique().tolist())}",
        )

        # 6. Tidak ada label 'Tidak tercatat'
        unknown_ch = int((self.df["channel"] == "Tidak tercatat").sum())
        self.log("Completeness", "Saluran masuk terisi lengkap (WhatsApp/Walk-In/OTA), 0 'Tidak tercatat'", unknown_ch == 0)

        # 7. Rekonsiliasi Omzet Bulanan terhadap PnL Aktual Owner
        if self.df_pnl is not None:
            monthly_synth = self.df.groupby("source_month")["total_price"].sum()
            monthly_pnl = pd.Series(self.df_pnl["omzet"].values, index=range(1, len(self.df_pnl) + 1))
            max_diff = int((monthly_synth - monthly_pnl).abs().max())
            tot_synth = int(self.df["total_price"].sum())
            tot_pnl = int(self.df_pnl["omzet"].sum())
            self.log(
                "Reconciliation",
                "Omzet bulanan & total dataset sintetis selaras 100% dengan rekap PnL owner",
                max_diff == 0 and tot_synth == tot_pnl,
                f"Total Sintetis: Rp {tot_synth:,} | Target Owner: Rp {tot_pnl:,} | Max selisih bulanan: Rp {max_diff:,}",
            )

        score = (self.passed_checks / self.total_checks) * 100
        print("=" * 72)
        print(f"SKOR AKHIR DQA: {score:.1f}% ({self.passed_checks}/{self.total_checks} Uji Lolos)")
        print("=" * 72)


if __name__ == "__main__":
    df = pd.read_csv(CLEAN_CSV)
    df_pnl = pd.read_csv(PNL_CSV) if os.path.exists(PNL_CSV) else None
    DataQualityChecker(df, df_pnl).run_all()
