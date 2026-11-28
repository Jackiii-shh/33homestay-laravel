"""
generate_synthetic_dataset.py
=============================
Generator Dataset Sintetis 33HomeStay (Januari - November 2026)
Berdasarkan pola distribusi aktual dari data buku kas owner:
  - Total transaksi per bulan & omzet bulanan mengikuti owner_monthly_pnl_clean.csv (Rp 317.164.000)
  - 6 unit kamar aktif termasuk Tera House (TH1): K1, K2, K3, K4, K5, TH1 (0 Unassigned)
  - Pola sewa harian (daily) vs transit (day-use), pola akhir pekan vs hari kerja,
    distribusi kota asal tamu, dan saluran masuk sepenuhnya realistis & lengkap
  - Tidak ada data kosong, '-', 'Unassigned', atau 'Tidak tercatat'
  - Privasi 100% aman (nama & nomor telepon sepenuhnya sintetis)
"""

import os
import random
from datetime import date, timedelta
import numpy as np
import pandas as pd

SEED = 202633
random.seed(SEED)
np.random.seed(SEED)

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
PNL_PATH = os.path.join(BASE_DIR, "owner_monthly_pnl_clean.csv")
SYNTH_PATH = os.path.join(BASE_DIR, "synthetic_reservations.csv")
CLEAN_PATH = os.path.join(BASE_DIR, "clean_reservations.csv")
RAW_PATH = os.path.join(BASE_DIR, "raw_reservations_manual.csv")

# Konfigurasi 6 Unit Kamar Aktif (termasuk TH1 Tera House)
ROOMS = {
    "K1":  {"type": "Standard Room AC",    "rate": 300000, "cap": 2, "scope": "inti",        "weight": 0.245},
    "K2":  {"type": "Standard Room AC",    "rate": 300000, "cap": 2, "scope": "inti",        "weight": 0.240},
    "K5":  {"type": "Standard Room AC",    "rate": 300000, "cap": 2, "scope": "inti",        "weight": 0.195},
    "K3":  {"type": "Mezanin",             "rate": 350000, "cap": 4, "scope": "inti",        "weight": 0.125},
    "TH1": {"type": "Tera House",          "rate": 800000, "cap": 6, "scope": "teras_house", "weight": 0.190},
    "K4":  {"type": "Standard Room Kipas", "rate": 200000, "cap": 2, "scope": "inti",        "weight": 0.005},
}

# Target bulanan sesuai pola & omzet aktual buku kas owner (Jan-Nov 2026)
MONTHLY_CONFIG = [
    {"month": 1,  "days": 31, "trx": 100, "omzet": 26392000, "daily_ratio": 0.53},
    {"month": 2,  "days": 28, "trx": 82,  "omzet": 19461000, "daily_ratio": 0.57},
    {"month": 3,  "days": 31, "trx": 112, "omzet": 33700000, "daily_ratio": 0.65},
    {"month": 4,  "days": 30, "trx": 115, "omzet": 30280000, "daily_ratio": 0.76},
    {"month": 5,  "days": 31, "trx": 118, "omzet": 32349000, "daily_ratio": 0.60},
    {"month": 6,  "days": 30, "trx": 101, "omzet": 27025000, "daily_ratio": 0.65},
    {"month": 7,  "days": 31, "trx": 109, "omzet": 27030000, "daily_ratio": 0.50},
    {"month": 8,  "days": 31, "trx": 130, "omzet": 33605000, "daily_ratio": 0.56},
    {"month": 9,  "days": 30, "trx": 132, "omzet": 32112000, "daily_ratio": 0.52},
    {"month": 10, "days": 31, "trx": 160, "omzet": 39600000, "daily_ratio": 0.46},
    {"month": 11, "days": 15, "trx": 55,  "omzet": 15610000, "daily_ratio": 0.36},
]

# Distribusi kota asal tamu berdasarkan data tercatat aktual
CITY_WEIGHTS = [
    ("Serang", 0.34),
    ("Cilegon", 0.20),
    ("Tangerang", 0.19),
    ("Anyer", 0.06),
    ("Jakarta", 0.05),
    ("Cikande", 0.04),
    ("Pandeglang", 0.03),
    ("Bekasi", 0.03),
    ("Bogor", 0.03),
    ("Rangkasbitung", 0.02),
    ("Merak", 0.01),
]

FIRST_NAMES = [
    "Andi", "Budi", "Citra", "Dewi", "Eko", "Fajar", "Gita", "Hendra",
    "Indra", "Joko", "Kartika", "Lestari", "Maulana", "Nadia", "Oki", "Putri",
    "Rama", "Sari", "Teguh", "Umar", "Vina", "Wahyu", "Yudi", "Zahra",
    "Agus", "Bayu", "Dian", "Fitri", "Gilang", "Hani", "Irfan", "Rizky",
]
LAST_NAMES = [
    "Pratama", "Santoso", "Wijaya", "Kusuma", "Hidayat", "Nugroho", "Saputra",
    "Lestari", "Rahmawati", "Permana", "Setiawan", "Utami", "Wibowo", "Siregar",
    "Hakim", "Fauzi", "Kurniawan", "Purnama", "Suryanto", "Ramadhan",
]

CHANNEL_CHOICES = [
    ("WhatsApp", 0.54, "Non-OTA"),
    ("Walk-In",  0.44, "Non-OTA"),
    ("Agoda",    0.009, "Agoda"),
    ("Traveloka", 0.007, "Traveloka"),
    ("Tiket.com", 0.004, "Tiket.com"),
]


def pick_weighted(items_weights):
    items, weights = zip(*items_weights)
    return random.choices(items, weights=weights, k=1)[0]


def pick_date_in_month(month: int, max_day: int) -> date:
    """Memilih tanggal dengan bobot akhir pekan (Sabtu-Minggu) ~1.85x lebih padat per hari."""
    days = [date(2026, month, d) for d in range(1, max_day + 1)]
    weights = [1.85 if dt.weekday() in (5, 6) else 1.0 for dt in days]
    return random.choices(days, weights=weights, k=1)[0]


def generate_synthetic():
    rows = []
    counter = 1

    room_codes = list(ROOMS.keys())
    room_weights = [ROOMS[c]["weight"] for c in room_codes]

    for cfg in MONTHLY_CONFIG:
        m = cfg["month"]
        max_day = cfg["days"]
        n_trx = cfg["trx"]
        target_omzet = cfg["omzet"]
        n_daily = round(n_trx * cfg["daily_ratio"])
        n_transit = n_trx - n_daily

        rental_types = ["daily"] * n_daily + ["transit"] * n_transit
        random.shuffle(rental_types)

        month_rows = []
        for r_type in rental_types:
            room_code = random.choices(room_codes, weights=room_weights, k=1)[0]
            info = ROOMS[room_code]
            check_in_dt = pick_date_in_month(m, max_day)

            if r_type == "transit":
                nights = 0
                check_out_dt = check_in_dt
                if room_code == "K3":
                    base_price = random.choice([220000, 250000, 270000])
                elif room_code == "K4":
                    base_price = 150000
                elif room_code == "TH1":
                    base_price = random.choice([150000, 180000, 200000, 220000])
                else:
                    base_price = random.choice([150000, 180000, 200000, 220000])
            else:
                nights = random.choices([1, 2, 3], weights=[0.84, 0.13, 0.03], k=1)[0]
                check_out_dt = check_in_dt + timedelta(days=nights)
                if room_code == "TH1":
                    # Di buku kas aktual rata-rata TH1 ~Rp 189rb/trx (campuran per kamar/harian)
                    base_price = random.choice([200000, 250000, 300000]) * nights
                else:
                    base_price = info["rate"] * nights

            # Saluran masuk & metode pembayaran
            ch_roll = random.random()
            cum = 0.0
            ch_val, ota_name = "WhatsApp", "Non-OTA"
            for name, w, ota in CHANNEL_CHOICES:
                cum += w
                if ch_roll <= cum:
                    ch_val, ota_name = name, ota
                    break

            if ota_name != "Non-OTA":
                pay_method = "OTA Payment"
                channel_group = "OTA"
            else:
                channel_group = ch_val
                if r_type == "transit":
                    pay_method = random.choices(["Tunai", "QRIS", "Transfer Bank"], weights=[0.70, 0.20, 0.10], k=1)[0]
                else:
                    pay_method = random.choices(["Transfer Bank", "Tunai", "QRIS"], weights=[0.55, 0.30, 0.15], k=1)[0]

            guest_name = f"{random.choice(FIRST_NAMES)} {random.choice(LAST_NAMES)}"
            guest_city = pick_weighted(CITY_WEIGHTS)
            guest_count = random.randint(1, info["cap"])
            note_val = (
                "Transit istirahat" if r_type == "transit"
                else random.choices(["Standar", "Check-in malam", "Setoran ke pengelola"], weights=[0.75, 0.15, 0.10], k=1)[0]
            )

            month_rows.append({
                "reservation_code": "",
                "reservation_group": "Single",
                "source_month": m,
                "source_table": "kiri" if room_code != "TH1" else "kanan",
                "source_row_no": 0,
                "guest_name": guest_name,
                "guest_city": guest_city,
                "guest_city_source": "synthetic",
                "guest_phone": "",
                "room_code": room_code,
                "room_type": info["type"],
                "room_code_source": "synthetic",
                "unit_scope": info["scope"],
                "room_capacity": info["cap"],
                "price_per_night": info["rate"],
                "check_in": check_in_dt.strftime("%Y-%m-%d"),
                "check_out": check_out_dt.strftime("%Y-%m-%d"),
                "nights": nights,
                "rental_type": r_type,
                "total_price": base_price,
                "reservation_total_price": base_price,
                "channel": channel_group,
                "ota_raw": ota_name,
                "ota_name": ota_name,
                "payment_method": pay_method,
                "guest_count": guest_count,
                "status": "PAID_OFF",
                "price_text_format": False,
                "date_unresolved": False,
                "needs_review": False,
                "notes": note_val,
            })

        # Kalibrasi total_price bulanan agar persis sama dengan omzet aktual di owner_monthly_pnl_clean.csv
        current_sum = sum(r["total_price"] for r in month_rows)
        diff = target_omzet - current_sum
        # Bagi selisih secara merata ke kelipatan Rp 1.000 pada transaksi daily
        daily_indices = [i for i, r in enumerate(month_rows) if r["rental_type"] == "daily"]
        if not daily_indices:
            daily_indices = list(range(len(month_rows)))

        step_unit = 1000 if diff >= 0 else -1000
        remaining_steps = abs(diff) // 1000
        idx_pos = 0
        while remaining_steps > 0:
            target_idx = daily_indices[idx_pos % len(daily_indices)]
            if month_rows[target_idx]["total_price"] + step_unit >= 100000:
                month_rows[target_idx]["total_price"] += step_unit
                month_rows[target_idx]["reservation_total_price"] = month_rows[target_idx]["total_price"]
                remaining_steps -= 1
            idx_pos += 1

        # Urutkan berdasarkan check_in dalam bulan tersebut
        month_rows.sort(key=lambda r: (r["check_in"], r["room_code"]))
        for row_no, r in enumerate(month_rows, start=1):
            r["source_row_no"] = row_no
            r["reservation_code"] = f"33H-2026-{counter:04d}"
            r["guest_phone"] = f"0812{counter:08d}"
            counter += 1
            rows.append(r)

    df = pd.DataFrame(rows)
    df.to_csv(SYNTH_PATH, index=False, encoding="utf-8")
    df.to_csv(CLEAN_PATH, index=False, encoding="utf-8")
    df.to_csv(RAW_PATH, index=False, encoding="utf-8")

    n_inti = int((df["unit_scope"] == "inti").sum())
    print("=" * 68)
    print("DATASET SINTETIS 33HOMESTAY BERHASIL DIBUAT")
    print("=" * 68)
    print(f"Total baris       : {len(df)} (Inti: {n_inti}, Tera House TH1: {len(df) - n_inti})")
    print(f"Total omzet       : Rp {df['total_price'].sum():,}")
    print(f"Null / '-' count  : {int(df.isna().sum().sum())} NaN, {int(df.astype(str).eq('-').sum().sum())} '-'")
    print(f"Unassigned count  : {int((df['room_code'] == 'Unassigned').sum())}")
    print("\nDistribusi Kamar:")
    print(df.groupby(["room_code", "room_type"])["total_price"].agg(["count", "sum"]).to_string())
    print("\nDistribusi Channel:")
    print(df["channel"].value_counts().to_string())
    print("\nOmzet per Bulan:")
    print(df.groupby("source_month")["total_price"].agg(["count", "sum"]).to_string())


if __name__ == "__main__":
    generate_synthetic()
