"""
Skrip Pembersihan Data Dummy Reservasi Manual 33HomeStay
Penanggung Jawab: Dzakwan Muzakki (Data Science - DS014B6Y0494)
Capstone Project: A26-CS026

Fungsi:
1. Membaca data mentah dari rekap manual buku tamu / WhatsApp di lapangan.
2. Standardisasi nama tamu, kota, dan format kontak WhatsApp (+62 / 08xx).
3. Pemetaan unit kamar tidak baku ke kode resmi sistem (AC-1, AC-2, AC-3, KPS-1, MZN-1, TH-1).
4. Normalisasi format tanggal beraneka ragam ke standar ISO-8601 (YYYY-MM-DD).
5. Validasi integritas harga, durasi malam, dan status pembayaran.
6. Menyimpan dataset bersih siap integrasi ke clean_reservations.csv.
"""

import os
import re
import pandas as pd
from datetime import datetime

CURRENT_DIR = os.path.dirname(os.path.abspath(__file__))
RAW_PATH = os.path.join(CURRENT_DIR, "raw_reservations_manual.csv")
CLEAN_PATH = os.path.join(CURRENT_DIR, "clean_reservations.csv")

def parse_flexible_date(date_str):
    if pd.isna(date_str):
        return None
    date_str = str(date_str).strip()
    formats = ["%Y-%m-%d", "%d/%m/%Y", "%d-%m-%Y", "%Y/%m/%d"]
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
        return "Lainnya"
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
    return city_str.strip().title()

def map_room(unit_str):
    u = str(unit_str).strip().lower()
    if "ac" in u:
        if "1" in u:
            return "AC-1", "Standard Room AC", 350000, 2
        elif "2" in u:
            return "AC-2", "Standard Room AC", 350000, 2
        elif "3" in u:
            return "AC-3", "Standard Room AC", 350000, 2
        return "AC-1", "Standard Room AC", 350000, 2
    elif "kipas" in u or "kps" in u:
        return "KPS-1", "Standard Room Kipas", 250000, 2
    elif "mezanin" in u or "mzn" in u or "mezzanine" in u:
        return "MZN-1", "Mezanin", 600000, 4
    elif "tera" in u or "th" in u:
        return "TH-1", "Tera House", 800000, 4
    return "AC-1", "Standard Room AC", 350000, 2

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
    df_clean["reservation_code"] = [f"33H-2026-{i+1:03d}" for i in range(len(df_raw))]

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
    df_clean["nights"] = (dt_out - dt_in).dt.days

    # Koreksi jika ada durasi <= 0
    df_clean["nights"] = df_clean["nights"].apply(lambda n: max(1, n))
    df_clean["total_price"] = df_clean["price_per_night"] * df_clean["nights"]

    # 8. Jumlah Tamu
    df_clean["guest_count"] = df_raw["jumlah_tamu"].clip(lower=1, upper=df_clean["room_capacity"])

    # 9. Status Pembayaran & Pembagian DP
    def map_status(st):
        s = str(st).lower()
        if "batal" in s or "refund" in s:
            return "CANCELLED"
        elif "dp" in s:
            return "DP_PAID"
        elif "lunas" in s:
            return "PAID_OFF"
        return "CONFIRMED"

    df_clean["status"] = df_raw["status_reservasi"].apply(map_status)
    df_clean["channel"] = df_raw["sumber_pemesanan"].str.strip().str.title()
    df_clean["payment_method"] = df_raw["metode_pembayaran"].str.strip()

    # Hitung DP (minimal 50%)
    df_clean["dp_amount"] = (df_clean["total_price"] * 0.5).astype(int)
    df_clean["remaining_payment"] = df_clean.apply(
        lambda r: 0 if r["status"] == "PAID_OFF" else (r["total_price"] - r["dp_amount"] if r["status"] == "DP_PAID" else 0),
        axis=1
    )

    df_clean["notes"] = df_raw["catatan_lapangan"].fillna("-")

    # Format phone explicitly as string with leading zero
    df_clean["guest_phone"] = df_clean["guest_phone"].astype(str).apply(lambda p: p if p.startswith("0") else "0" + p)

    # Simpan ke CSV bersih dengan quoting nonnumeric
    df_clean.to_csv(CLEAN_PATH, index=False, quoting=1) # csv.QUOTE_ALL
    print(f"Data bersih berhasil disimpan ke: {CLEAN_PATH}")
    print(f"Total baris data bersih: {len(df_clean)}")
    print("\nRingkasan Statistik Hasil Cleaning:")
    print(f"- Total Pendapatan Tercatat: Rp {df_clean[df_clean['status'] != 'CANCELLED']['total_price'].sum():,}")
    print(f"- Reservasi Lunas: {(df_clean['status'] == 'PAID_OFF').sum()}")
    print(f"- Reservasi DP 50%: {(df_clean['status'] == 'DP_PAID').sum()}")
    print(f"- Reservasi Batal: {(df_clean['status'] == 'CANCELLED').sum()}")
    print("=" * 65)

if __name__ == "__main__":
    main()
