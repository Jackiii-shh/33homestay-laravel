"""
impute_missing_values.py
========================
Mengisi nilai '-' dan NaN di clean_reservations.csv menggunakan metode
data science yang sesuai per kolom.

Metode per kolom:
  1. room_code & room_type  → Rule-Based Inference (berdasarkan price_per_night & rental_type)
  2. guest_city             → Mode Imputation (modus dari data yang sudah terisi)
  3. payment_method         → Proxy Imputation (berdasarkan channel & rental_type)
  4. guest_phone            → Data Masking ('08XXXXXXXXXX')
  5. notes                  → Constant Replacement ('Standar')

Output: overwrite clean_reservations.csv & raw_reservations_manual.csv
"""

import pandas as pd
import numpy as np
from pathlib import Path

BASE_DIR = Path(__file__).parent
CSV_PATH = BASE_DIR / "clean_reservations.csv"
RAW_PATH = BASE_DIR / "raw_reservations_manual.csv"

# ─── Load ───────────────────────────────────────────────────────────────────
df = pd.read_csv(CSV_PATH)

print(f"CSV dimuat: {len(df)} baris, {len(df.columns)} kolom")
print("=" * 60)

# Normalise: ganti '-' string kosong menjadi NaN untuk memudahkan operasi
DASH = "-"

def is_missing(series: pd.Series) -> pd.Series:
    """True jika nilai adalah NaN, string '-', atau string kosong."""
    return series.isna() | series.astype(str).str.strip().isin([DASH, "", "nan"])

# Hitung kondisi awal
kondisi_awal = {col: int(is_missing(df[col]).sum()) for col in df.columns}
print("\n[KONDISI AWAL] Jumlah nilai kosong/'-' per kolom:")
for col, n in kondisi_awal.items():
    if n > 0:
        print(f"  {col:<25} : {n}")

# ─── 1. room_code & room_type — Rule-Based Inference ────────────────────────
print("\n[1] Rule-Based Inference untuk room_code & room_type ...")

TARIF_K3        = 350_000
TARIF_K4_DAILY  = 200_000
TARIF_STANDARD  = 300_000

mask_no_room = is_missing(df["room_code"]) | is_missing(df["room_type"])

isi_room = 0
for idx in df[mask_no_room].index:
    harga  = int(df.at[idx, "price_per_night"])
    r_type = str(df.at[idx, "rental_type"]).lower()

    if harga == TARIF_K3:
        code, rtype, cap = "K3", "Mezanin", 4
    elif harga == TARIF_K4_DAILY and r_type == "daily":
        code, rtype, cap = "K4", "Standard Room Kipas", 2
    elif harga >= TARIF_STANDARD or r_type == "transit":
        code, rtype, cap = "Unassigned", "Standard Room AC", 2
    else:
        code, rtype, cap = "Unassigned", "Standard Room AC", 2

    df.at[idx, "room_code"]     = code
    df.at[idx, "room_type"]     = rtype
    df.at[idx, "room_capacity"] = cap
    isi_room += 1

print(f"  → {isi_room} baris room_code/room_type diisi")

# ─── 2. guest_city — Mode Imputation ────────────────────────────────────────
print("\n[2] Mode Imputation untuk guest_city ...")

terisi_mask = ~is_missing(df["guest_city"])
modus_kota  = df.loc[terisi_mask, "guest_city"].mode()
modus_kota  = modus_kota.iloc[0] if len(modus_kota) > 0 else "Serang"

print(f"  Modus kota dari data terisi: '{modus_kota}'")

isi_kota = 0
kosong_kota = is_missing(df["guest_city"])
df.loc[kosong_kota, "guest_city"] = modus_kota
isi_kota = int(kosong_kota.sum())

print(f"  → {isi_kota} baris guest_city diisi dengan '{modus_kota}'")

# ─── 3. payment_method — Proxy Imputation ───────────────────────────────────
print("\n[3] Proxy Imputation untuk payment_method ...")

isi_payment = 0
kosong_pm = is_missing(df["payment_method"])
for idx in df[kosong_pm].index:
    channel = str(df.at[idx, "channel"]).strip().upper()
    r_type  = str(df.at[idx, "rental_type"]).strip().lower()

    if "OTA" in channel:
        pm = "OTA Payment / Virtual Account"
    elif r_type == "transit":
        pm = "Tunai / Cash"
    else:
        pm = "Transfer / Tunai"

    df.at[idx, "payment_method"] = pm
    isi_payment += 1

print(f"  → {isi_payment} baris payment_method diisi")

# ─── 4. guest_phone — Data Masking ──────────────────────────────────────────
print("\n[4] Data Masking untuk guest_phone ...")

PLACEHOLDER_PHONE = "08XXXXXXXXXX"
kosong_phone = is_missing(df["guest_phone"])
df.loc[kosong_phone, "guest_phone"] = PLACEHOLDER_PHONE
isi_phone = int(kosong_phone.sum())
print(f"  → {isi_phone} baris guest_phone diisi dengan '{PLACEHOLDER_PHONE}'")

# ─── 5. notes — Constant Replacement ────────────────────────────────────────
print("\n[5] Constant Replacement untuk notes ...")

PLACEHOLDER_NOTES = "Standar"
kosong_notes = is_missing(df["notes"])
df.loc[kosong_notes, "notes"] = PLACEHOLDER_NOTES
isi_notes = int(kosong_notes.sum())
print(f"  → {isi_notes} baris notes diisi dengan '{PLACEHOLDER_NOTES}'")

# ─── Verifikasi akhir ────────────────────────────────────────────────────────
print("\n" + "=" * 60)
print("[VERIFIKASI] Sisa nilai kosong/'-' setelah imputasi:")
kondisi_akhir = {col: int(is_missing(df[col]).sum()) for col in df.columns}
ada_sisa = False
for col, n in kondisi_akhir.items():
    if n > 0:
        print(f"  {col:<25} : {n}  ⚠️")
        ada_sisa = True
if not ada_sisa:
    print("  ✅ Tidak ada nilai kosong atau '-' yang tersisa!")

# ─── Ringkasan imputasi ──────────────────────────────────────────────────────
print("\n[RINGKASAN IMPUTASI]")
print(f"  room_code / room_type  : {isi_room} baris (Rule-Based Inference)")
print(f"  guest_city             : {isi_kota} baris (Mode Imputation → '{modus_kota}')")
print(f"  payment_method         : {isi_payment} baris (Proxy Imputation)")
print(f"  guest_phone            : {isi_phone} baris (Data Masking → '08XXXXXXXXXX')")
print(f"  notes                  : {isi_notes} baris (Constant Replacement → 'Standar')")
total_diisi = isi_room + isi_kota + isi_payment + isi_phone + isi_notes
print(f"  {'─'*40}")
print(f"  TOTAL                  : {total_diisi} pengisian")

# ─── Distribusi payment_method setelah imputasi ──────────────────────────────
print("\n[INFO] Distribusi payment_method setelah imputasi:")
print(df["payment_method"].value_counts().to_string())

# ─── Distribusi room_code setelah imputasi ───────────────────────────────────
print("\n[INFO] Distribusi room_code setelah imputasi:")
print(df["room_code"].value_counts().to_string())

# ─── Simpan CSV ─────────────────────────────────────────────────────────────
df.to_csv(CSV_PATH, index=False, encoding="utf-8")
df.to_csv(RAW_PATH, index=False, encoding="utf-8")

print(f"\n✅ CSV disimpan ke:")
print(f"   {CSV_PATH}")
print(f"   {RAW_PATH}")
print(f"\nTotal baris: {len(df)} | Total kolom: {len(df.columns)}")
