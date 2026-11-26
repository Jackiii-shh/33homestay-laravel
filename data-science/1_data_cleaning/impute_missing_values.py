"""
impute_missing_values.py
========================
Modul Imputasi Nilai Kosong Sesuai Kebijakan Proyek 33HomeStay:
- K-1: guest_city menggunakan Mode Imputation dari data terisi (modus: 'Serang')
- K-2: room_code & room_type tabel kiri kosong menggunakan Rule-Based Inference:
       Rp 350.000 -> K3 (Mezanin)
       Rp 200.000 (daily) -> K4 (Standard Room Kipas)
       Rp 300.000 / transit -> Unassigned (Standard Room AC)
- N-1: Tabel kanan adalah unit Teras House (TH1), KECUALI yang tertulis eksplisit K4.
- Tugas 5: Kolom karangan (payment_method, dp, remaining, guest_count=2, channel='Direct')
           TIDAK ditambahkan / dinetralkan sesuai arahan bisnis.

Catatan: Logika ini telah diintegrasikan langsung dalam pipeline ekstraksi
extract_actual_all_months.py dengan penanda asal (provenance) lengkap.
Skrip ini memverifikasi integritas imputasi pada clean_reservations.csv.
"""

import os
import pandas as pd

DATA_DIR = os.path.dirname(os.path.abspath(__file__))
CLEAN_CSV = os.path.join(DATA_DIR, "clean_reservations.csv")

def verify_imputations():
    if not os.path.exists(CLEAN_CSV):
        print(f"[!] File {CLEAN_CSV} belum dibuat. Jalankan extract_actual_all_months.py terlebih dahulu.")
        return

    df = pd.read_csv(CLEAN_CSV)
    print("=" * 60)
    print("VERIFIKASI IMPUTASI DATA RESERVASI 33HOMESTAY")
    print("=" * 60)
    print(f"Total baris: {len(df)}")
    
    # 1. K-1: guest_city
    print("\n[1] Verifikasi K-1 (guest_city):")
    print(df['guest_city_source'].value_counts())
    print(f"Modus terpakai pada imputed_mode: {df[df['guest_city_source'] == 'imputed_mode']['guest_city'].value_counts().to_dict()}")
    
    # 2. K-2: room_code & N-1: Teras House
    print("\n[2] Verifikasi K-2 & N-1 (room_code & source):")
    print(pd.crosstab(df['unit_scope'], df['room_code_source']))
    
    # 3. Tugas 5: Pemeriksaan ketiadaan kolom karangan
    print("\n[3] Verifikasi Tugas 5 (Pembersihan Kolom Karangan):")
    banned_cols = ['payment_method', 'dp_amount', 'remaining_payment']
    found_banned = [c for c in banned_cols if c in df.columns]
    if found_banned:
        print(f"  [PERINGATAN] Ditemukan kolom karangan yang dilarang: {found_banned}")
    else:
        print("  [OK] Kolom karangan (payment_method, dp_amount, remaining_payment) tidak ada di dataset analisis.")
    
    if (df['channel'] == 'Direct').any():
        print("  [PERINGATAN] Ditemukan kanal 'Direct'!")
    else:
        print("  [OK] Tidak ada kanal karangan 'Direct'. Kanal kosong ditandai 'Tidak tercatat'.")
        
    print("\nVerifikasi selesai.")
    print("=" * 60)

if __name__ == "__main__":
    verify_imputations()
