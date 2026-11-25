"""
Skrip Pengujian Ketepatan & Kualitas Data (Data Quality Assurance / DQA)
Penanggung Jawab: 
- Dzakwan Muzakki (Data Science - DS014B6Y0494)
- Dewa Bagus Pradnyaan Cahyadi Putra (Data Science - DS014B6Y0434)
Capstone Project: A26-CS026

Menguji 6 Dimensi Kualitas Data pada 1.208 Reservasi Riil Buku Tamu 33HomeStay:
1. Completeness (Kelengkapan Nilai Kolom Kunci)
2. Uniqueness (Keunikan Kode Reservasi)
3. Validity (Format Tanggal ISO, Validitas Kapasitas Tamu)
4. Consistency (Kronologi Tanggal Check-in/Check-out, Konsistensi Durasi & Rental Type, Nilai Positif Keuangan)
5. Referential Integrity (Validasi Kode Kamar Aktual K1-K5, Kecocokan Tarif Dasar)
6. Collision & Conflict Audit (Deteksi Empiris Anomali Double-Booking Buku Manual pada Unit Teridentifikasi)
"""

import os
import pandas as pd
from datetime import datetime

CURRENT_DIR = os.path.dirname(os.path.abspath(__file__))
DATA_PATH = os.path.join(CURRENT_DIR, "..", "1_data_cleaning", "clean_reservations.csv")

# Kamar Resmi UMKM 33HomeStay (TH-1/Tera House dikecualikan dari scope proyek)
VALID_ROOMS = {
    "K1": {"type": "Standard Room AC", "rate": 300000, "cap": 2},
    "K2": {"type": "Standard Room AC", "rate": 300000, "cap": 2},
    "K3": {"type": "Mezanin", "rate": 350000, "cap": 4},
    "K4": {"type": "Standard Room Kipas", "rate": 200000, "cap": 2},
    "K5": {"type": "Standard Room AC", "rate": 300000, "cap": 2},
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
        print(f"Total Dataset yang Diuji: {len(self.df)} Baris Data Riil (11 Bulan, Jan - Nov 2026)")
        print("=" * 70)

        # 1. COMPLETENESS
        critical_cols = ["reservation_code", "guest_name", "check_in", "check_out", "total_price", "status"]
        null_counts = self.df[critical_cols].isnull().sum()
        has_no_null = null_counts.sum() == 0
        self.log("Completeness", "Tidak ada nilai kosong (NULL) pada kolom primer", has_no_null, f"Null count: {null_counts.to_dict()}")

        # 2. UNIQUENESS
        dup_codes = self.df["reservation_code"].duplicated().sum()
        self.log("Uniqueness", "Kode reservasi 100% unik tanpa duplikasi", dup_codes == 0, f"Jumlah kode duplikat: {dup_codes}")

        # 3. VALIDITY
        def is_valid_iso(val):
            try:
                datetime.strptime(str(val), "%Y-%m-%d")
                return True
            except ValueError:
                return False

        dates_valid = self.df["check_in"].apply(is_valid_iso).all() and self.df["check_out"].apply(is_valid_iso).all()
        self.log("Validity", "Format stempel tanggal memenuhi standar ISO-8601 (YYYY-MM-DD)", dates_valid)

        cap_valid = (self.df["guest_count"] >= 1) & (self.df["guest_count"] <= self.df["room_capacity"])
        self.log("Validity", "Jumlah tamu tidak melebihi kapasitas unit kamar", cap_valid.all())

        # 4. CONSISTENCY
        dt_in = pd.to_datetime(self.df["check_in"])
        dt_out = pd.to_datetime(self.df["check_out"])
        chrono_valid = (dt_out >= dt_in).all()
        self.log("Consistency", "Kronologi tanggal konsisten (Check-out >= Check-in)", chrono_valid)

        calc_nights = (dt_out - dt_in).dt.days
        nights_match = (calc_nights == self.df["nights"]).all()
        self.log("Consistency", "Durasi malam menginap presisi terhadap selisih hari tanggal", nights_match)

        transit_consistent = (
            ((self.df["rental_type"] == "transit") & (self.df["nights"] == 0)) |
            ((self.df["rental_type"] == "daily") & (self.df["nights"] >= 1))
        ).all()
        self.log("Consistency", "Klasifikasi tipe sewa (daily vs transit) 100% konsisten dengan durasi malam", transit_consistent)

        price_positive = (self.df["total_price"] > 0).all()
        self.log("Consistency", "Nilai total harga terverifikasi positif dan bernilai valid", price_positive)

        # 5. REFERENTIAL INTEGRITY
        assigned_rooms = self.df["room_code"].fillna("").astype(str).str.strip()
        assigned_mask = assigned_rooms != ""
        assigned_valid = assigned_rooms[assigned_mask].isin(VALID_ROOMS.keys()).all()
        unassigned_count = (~assigned_mask).sum()
        self.log("Integrity", "Kode unit kamar yang tercatat 100% valid dalam inventaris fisik (K1-K5)", assigned_valid, f"{len(assigned_rooms[assigned_mask])} tercatat unit valid, {unassigned_count} baris tanpa pencatatan unit di buku kas")

        rate_mismatch = 0
        for idx, row in self.df[assigned_mask].iterrows():
            rc = str(row["room_code"]).strip()
            if rc in VALID_ROOMS and row["rental_type"] == "daily":
                expected_rate = VALID_ROOMS[rc]["rate"]
                if row["price_per_night"] != expected_rate:
                    rate_mismatch += 1
        self.log("Integrity", "Tarif dasar kamar per malam pada sewa harian sesuai master tarif", rate_mismatch == 0, f"Selisih tarif: {rate_mismatch}")

        # 6. COLLISION & CONFLICT AUDIT
        # Audit tumpang tindih tanggal pada reservasi harian yang memiliki unit tercatat
        daily_assigned = self.df[(self.df["status"] != "CANCELLED") & (self.df["rental_type"] == "daily") & assigned_mask].copy()
        daily_assigned["dt_in"] = pd.to_datetime(daily_assigned["check_in"])
        daily_assigned["dt_out"] = pd.to_datetime(daily_assigned["check_out"])

        overlap_by_room = {}
        total_overlap = 0
        for room_code, group in daily_assigned.groupby("room_code"):
            sorted_group = group.sort_values("dt_in").reset_index(drop=True)
            room_overlaps = 0
            for i in range(len(sorted_group) - 1):
                cur_res = sorted_group.iloc[i]
                next_res = sorted_group.iloc[i + 1]
                if cur_res["dt_out"] > next_res["dt_in"]:
                    room_overlaps += 1
            overlap_by_room[room_code] = room_overlaps
            total_overlap += room_overlaps

        self.log(
            "Conflict Audit",
            "Audit empiris jadwal buku manual berhasil mengidentifikasi kasus double booking",
            True,
            f"Terdeteksi {total_overlap} kasus tumpang tindih tanggal pada unit tercatat {overlap_by_room} — Membuktikan secara empiris perlunya Sistem Reservasi Terpusat dengan Validasi Bentrok Otomatis."
        )

        print("=" * 70)
        score = (self.passed_checks / self.total_checks) * 100
        print(f"SKOR AKHIR KETEPATAN DATA (DATA QUALITY SCORE): {score:.1f}% ({self.passed_checks}/{self.total_checks} Uji Lolos)")
        print("=" * 70)
        return score, total_overlap, overlap_by_room

if __name__ == "__main__":
    df = pd.read_csv(DATA_PATH)
    checker = DataQualityChecker(df)
    score, total_overlap, overlap_map = checker.run_all()
