"""
Skrip Pembersihan Data Reservasi Manual 33HomeStay
Penanggung Jawab: Dzakwan Muzakki (Data Science - DS014B6Y0494)
Capstone Project: A26-CS026

Fungsi:
1. Membaca data mentah dari rekap manual buku tamu / WhatsApp di lapangan (raw_reservations_manual.csv).
2. Standardisasi nama tamu, kota, dan format kontak WhatsApp (+62 / 08xx).
3. Pemetaan unit kamar tidak baku ke kode resmi sistem (K1, K2, K3, K4, K5, TH-1).
4. Normalisasi format tanggal beraneka ragam ke standar ISO-8601 (YYYY-MM-DD).
5. Validasi integritas harga, durasi malam, dan status sewa (daily vs transit).
6. Menyimpan dataset bersih siap integrasi ke clean_reservations.csv.
"""

import os
import re
import csv
import pandas as pd
from datetime import datetime

CURRENT_DIR = os.path.dirname(os.path.abspath(__file__))
RAW_PATH = os.path.join(CURRENT_DIR, "raw_reservations_manual.csv")
CLEAN_PATH = os.path.join(CURRENT_DIR, "clean_reservations.csv")

def parse_flexible_date(date_str):
    if pd.isna(date_str):
        return None
    date_str = str(date_str).strip()
    formats = ["%Y-%m-%d", "%d/%m/%Y", "%d-%m-%Y", "%Y/%m/%d", "%m/%d/%Y"]
    for fmt in formats:
        try:
            return datetime.strptime(date_str, fmt).strftime("%Y-%m-%d")
        except ValueError:
            continue
    return None

def clean_phone(phone_str):
    if pd.isna(phone_str):
        return ""
    digits = re.sub(r"\D", "", str(phone_str))
    if digits.startswith("62"):
        digits = "0" + digits[2:]
    elif digits.startswith("8"):
        digits = "0" + digits
    return digits

def map_city(city_str):
    if pd.isna(city_str):
        return "Serang"
    c = str(city_str).strip().lower()
    if "jkt" in c or "jakarta" in c:
        return "Jakarta"
    if "bks" in c or "bekasi" in c:
        return "Bekasi"
    if "tgr" in c or "tangsel" in c or "tangerang" in c:
        return "Tangerang"
    if "bgr" in c or "bogor" in c:
        return "Bogor"
    if "depok" in c:
        return "Depok"
    if "serang" in c:
        return "Serang"
    if "clg" in c or "cilegon" in c:
        return "Cilegon"
    if "bdg" in c or "bandung" in c:
        return "Bandung"
    if "pandeglang" in c:
        return "Pandeglang"
    if "anyer" in c:
        return "Anyer"
    return city_str.strip().title()

def map_room(unit_str):
    u = str(unit_str).strip().upper()
    if "K1" in u:
        return "K1", "Standard Room AC", 300000, 2
    elif "K2" in u:
        return "K2", "Standard Room AC", 300000, 2
    elif "K3" in u or "MEZANIN" in u:
        return "K3", "Mezanin", 350000, 4
    elif "K4" in u or "KIPAS" in u:
        return "K4", "Standard Room Kipas", 200000, 2
    elif "K5" in u:
        return "K5", "Standard Room AC", 300000, 2
    elif "TH" in u or "TERA" in u:
        return "TH-1", "Tera House", 800000, 4
    return "K1", "Standard Room AC", 300000, 2

def main():
    print("=" * 65)
    print("PROSES DATA CLEANING RESERVASI MANUAL 33HOMESTAY")
    print("=" * 65)

    if not os.path.exists(RAW_PATH):
        raise FileNotFoundError(f"File mentah tidak ditemukan: {RAW_PATH}")

    df_raw = pd.read_csv(RAW_PATH)
    print(f"Total data mentah: {len(df_raw)} baris")

    df_clean = pd.DataFrame()

    # 1. Standardisasi ID
    df_clean["reservation_code"] = [f"33H-2026-{i+1:04d}" for i in range(len(df_raw))]

    # 2. Standardisasi Nama Tamu
    df_clean["guest_name"] = df_raw["nama_pelanggan"].astype(str).str.strip().str.title()

    # 3. Standardisasi Kota
    df_clean["guest_city"] = df_raw["kota_asal"].apply(map_city)

    # 4. Standardisasi Kontak WhatsApp
    df_clean["guest_phone"] = df_raw["kontak_hp"].apply(clean_phone)

    # 5. Pemetaan Kamar & Tipe
    room_meta = df_raw["tipe_unit"].apply(map_room)
    df_clean["room_code"] = [r[0] for r in room_meta]
    df_clean["room_type"] = [r[1] for r in room_meta]
    df_clean["price_per_night"] = [r[2] for r in room_meta]
    df_clean["room_capacity"] = [r[3] for r in room_meta]

    # 6. Normalisasi Tanggal
    df_clean["check_in"] = df_raw["tgl_masuk"].apply(parse_flexible_date)
    df_clean["check_out"] = df_raw["tgl_keluar"].apply(parse_flexible_date)

    # 7. Validasi Durasi Malam & Total Harga
    dt_in = pd.to_datetime(df_clean["check_in"])
    dt_out = pd.to_datetime(df_clean["check_out"])
    df_clean["nights"] = (dt_out - dt_in).dt.days.clip(lower=0)
    
    # Rental Type: transit jika 0 malam
    df_clean["rental_type"] = df_clean["nights"].apply(lambda n: "transit" if n == 0 else "daily")
    
    # Total Price: gunakan total_biaya aktual dari raw jika ada
    if "total_biaya" in df_raw.columns:
        df_clean["total_price"] = pd.to_numeric(df_raw["total_biaya"], errors="coerce").fillna(0).astype(int)
        # Jika nol, fallback
        df_clean["total_price"] = df_clean.apply(
            lambda r: 220000 if r["rental_type"] == "transit" and r["total_price"] <= 0
            else (r["price_per_night"] * max(1, r["nights"]) if r["total_price"] <= 0 else r["total_price"]),
            axis=1
        )
    else:
        df_clean["total_price"] = df_clean["price_per_night"] * df_clean["nights"].apply(lambda n: max(1, n))

    # 8. Jumlah Tamu
    df_clean["guest_count"] = df_raw["jumlah_tamu"].clip(lower=1, upper=df_clean["room_capacity"])

    # 9. Status Pembayaran
    def map_status(st):
        s = str(st).lower()
        if "batal" in s or "refund" in s or "cancel" in s:
            return "CANCELLED"
        elif "dp" in s:
            return "DP_PAID"
        elif "lunas" in s or "paid" in s:
            return "PAID_OFF"
        return "PAID_OFF"

    df_clean["status"] = df_raw["status_reservasi"].apply(map_status)
    df_clean["channel"] = df_raw["sumber_pemesanan"].str.strip().str.title()
    df_clean["payment_method"] = df_raw["metode_pembayaran"].str.strip()

    # Hitung DP
    df_clean["dp_amount"] = df_clean.apply(
        lambda r: r["total_price"] if "ota" in str(r["channel"]).lower() else int(r["total_price"] * 0.5),
        axis=1
    )
    df_clean["remaining_payment"] = df_clean.apply(
        lambda r: 0 if r["status"] == "PAID_OFF" else (r["total_price"] - r["dp_amount"] if r["status"] == "DP_PAID" else 0),
        axis=1
    )

    df_clean["notes"] = df_raw["catatan_lapangan"].fillna("-")
    df_clean["guest_phone"] = df_clean["guest_phone"].astype(str).apply(lambda p: p if p.startswith("0") else "0" + p)

    # Simpan ke CSV bersih
    df_clean.to_csv(CLEAN_PATH, index=False, quoting=csv.QUOTE_ALL)
    print(f"Data bersih berhasil disimpan ke: {CLEAN_PATH}")
    print(f"Total baris data bersih: {len(df_clean)}")
    print("\nRingkasan Statistik Hasil Cleaning:")
    print(f"- Total Pendapatan Tercatat: Rp {df_clean[df_clean['status'] != 'CANCELLED']['total_price'].sum():,}")
    print(f"- Reservasi Lunas   : {(df_clean['status'] == 'PAID_OFF').sum()}")
    print(f"- Reservasi Transit : {(df_clean['rental_type'] == 'transit').sum()}")
    print(f"- Reservasi Daily   : {(df_clean['rental_type'] == 'daily').sum()}")
    print("=" * 65)

if __name__ == "__main__":
    main()
