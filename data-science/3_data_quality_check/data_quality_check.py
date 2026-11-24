"""
Skrip Pengujian Ketepatan & Kualitas Data (Data Quality Assurance / DQA)
Penanggung Jawab: 
- Dzakwan Muzakki (Data Science - DS014B6Y0494)
- Dewa Bagus Pradnyaan Cahyadi Putra (Data Science - DS014B6Y0434)
Capstone Project: A26-CS026

Menguji 6 Dimensi Kualitas Data:
1. Completeness (Kelengkapan Nilai)
2. Uniqueness (Keunikan Kunci Transaksi)
3. Validity (Kesesuaian Format & Batasan Nilai)
4. Consistency (Konsistensi Relasi Tanggal & Kalkulasi Keuangan)
5. Referential Integrity (Integritas Kode Kamar & Kapasitas)
6. Anti-Double-Booking Verification (Nol Interval Waktu Tumpang Tindih)
"""

import os
import pandas as pd
from datetime import datetime

CURRENT_DIR = os.path.dirname(os.path.abspath(__file__))
DATA_PATH = os.path.join(CURRENT_DIR, "..", "1_data_cleaning", "clean_reservations.csv")

VALID_ROOMS = {
    "AC-1": {"type": "Standard Room AC", "rate": 350000, "cap": 2},
    "AC-2": {"type": "Standard Room AC", "rate": 350000, "cap": 2},
    "AC-3": {"type": "Standard Room AC", "rate": 350000, "cap": 2},
    "KPS-1": {"type": "Standard Room Kipas", "rate": 250000, "cap": 2},
    "MZN-1": {"type": "Mezanin", "rate": 600000, "cap": 4},
    "TH-1": {"type": "Tera House", "rate": 800000, "cap": 4},
}

class DataQualityChecker:
    def __init__(self, df):
        self.df = df
        self.results = []
        self.total_checks = 0
        self.passed_checks = 0

    def log(self, dim, desc, passed, details=""):
        self.total_checks += 1
        if passed:
            self.passed_checks += 1
        status_str = "PASS [OK]" if passed else "FAIL [X]"
        self.results.append({
            "dimension": dim,
            "description": desc,
            "status": status_str,
            "passed": passed,
            "details": details
        })
        print(f"[{status_str}] {dim} - {desc}")
        if details:
            print(f"        Detail: {details}")

    def run_all(self):
        print("=" * 70)
        print("AUDIT KETEPATAN DATA (DATA QUALITY CHECK) 33HOMESTAY")
        print("=" * 70)

        # 1. COMPLETENESS
        critical_cols = ["reservation_code", "guest_name", "guest_phone", "room_code", "check_in", "check_out", "total_price", "status"]
        null_counts = self.df[critical_cols].isnull().sum()
        has_no_null = null_counts.sum() == 0
        self.log("Completeness", "Tidak ada nilai kosong (NULL) pada kolom primer", has_no_null, f"Null count: {null_counts.to_dict()}")

        # 2. UNIQUENESS
        dup_codes = self.df["reservation_code"].duplicated().sum()
        self.log("Uniqueness", "Kode reservasi 100% unik tanpa duplikasi", dup_codes == 0, f"Jumlah kode duplikat: {dup_codes}")

        # 3. VALIDITY
        # Valid phone format
        valid_phones = self.df["guest_phone"].astype(str).str.match(r"^08\d{8,12}$").all()
        self.log("Validity", "Format nomor kontak sesuai kaidah telepon Indonesia (08xxxxxxxxx)", valid_phones)

        # Valid date formats
        def is_valid_iso(val):
            try:
                datetime.strptime(str(val), "%Y-%m-%d")
                return True
            except ValueError:
                return False

        dates_valid = self.df["check_in"].apply(is_valid_iso).all() and self.df["check_out"].apply(is_valid_iso).all()
        self.log("Validity", "Format stempel tanggal memenuhi standar ISO-8601 (YYYY-MM-DD)", dates_valid)

        # Capacity validity
        cap_valid = (self.df["guest_count"] >= 1) & (self.df["guest_count"] <= self.df["room_capacity"])
        self.log("Validity", "Jumlah tamu tidak melebihi kapasitas resmi unit kamar", cap_valid.all())

        # 4. CONSISTENCY
        # check_out > check_in
        dt_in = pd.to_datetime(self.df["check_in"])
        dt_out = pd.to_datetime(self.df["check_out"])
        chrono_valid = (dt_out > dt_in).all()
        self.log("Consistency", "Kronologi tanggal konsisten (Check-out selalu setelah Check-in)", chrono_valid)

        # Duration match
        calc_nights = (dt_out - dt_in).dt.days
        nights_match = (calc_nights == self.df["nights"]).all()
        self.log("Consistency", "Durasi malam menginap presisi terhadap selisih hari tanggal", nights_match)

        # Price calculation match
        calc_price = self.df["price_per_night"] * self.df["nights"]
        price_match = (calc_price == self.df["total_price"]).all()
        self.log("Consistency", "Total harga konsisten dengan (Tarif Kamar x Durasi Malam)", price_match)

        # 5. REFERENTIAL INTEGRITY
        valid_room_codes = self.df["room_code"].isin(VALID_ROOMS.keys()).all()
        self.log("Integrity", "Kode unit kamar terdaftar resmi dalam inventaris fisik 33HomeStay", valid_room_codes)

        # Room rate match reference
        rate_mismatch = 0
        for idx, row in self.df.iterrows():
            if row["room_code"] in VALID_ROOMS:
                if row["price_per_night"] != VALID_ROOMS[row["room_code"]]["rate"]:
                    rate_mismatch += 1
        self.log("Integrity", "Tarif dasar kamar per malam 100% cocok dengan master catalog tarif", rate_mismatch == 0)

        # 6. ANTI-DOUBLE-BOOKING OVERLAP VERIFICATION
        active_df = self.df[self.df["status"] != "CANCELLED"].copy()
        active_df["dt_in"] = pd.to_datetime(active_df["check_in"])
        active_df["dt_out"] = pd.to_datetime(active_df["check_out"])

        overlap_detected = False
        overlap_details = []

        for room_code, group in active_df.groupby("room_code"):
            sorted_group = group.sort_values("dt_in").reset_index(drop=True)
            for i in range(len(sorted_group) - 1):
                cur_res = sorted_group.iloc[i]
                next_res = sorted_group.iloc[i + 1]
                # Overlap condition: cur.dt_out > next.dt_in
                if cur_res["dt_out"] > next_res["dt_in"]:
                    overlap_detected = True
                    overlap_details.append(
                        f"Kamar {room_code}: {cur_res['reservation_code']} ({cur_res['check_in']} s/d {cur_res['check_out']}) BENTROK dengan {next_res['reservation_code']} ({next_res['check_in']} s/d {next_res['check_out']})"
                    )

        self.log(
            "Anti-Double-Booking",
            "Verifikasi matematis nol interval tumpang tindih pada kamar yang sama",
            not overlap_detected,
            "Tidak ada bentrok jadwal ditemukan." if not overlap_detected else "; ".join(overlap_details)
        )

        print("=" * 70)
        score = (self.passed_checks / self.total_checks) * 100
        print(f"SKOR AKHIR KETEPATAN DATA (DATA QUALITY SCORE): {score:.1f}% ({self.passed_checks}/{self.total_checks} Uji Lolos)")
        print("=" * 70)
        return score

if __name__ == "__main__":
    df = pd.read_csv(DATA_PATH, dtype={"guest_phone": str})
    checker = DataQualityChecker(df)
    score = checker.run_all()
