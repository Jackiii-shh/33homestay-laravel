"""
Kompilasi Data Riil Buku Kas Owner 33HomeStay (Januari - November 2026)
Penanggung Jawab: Dzakwan Muzakki (Data Science - DS014B6Y0494)
Kategori: Deliverable Wajib (Pengolahan & Pembersihan Data Riil UMKM)

Sumber Data: Laporan Buku Kas Bulanan Aktual Mitra 33HomeStay (Kak Taufiq)
Kamar:
- K1 = Standard Room AC
- K2 = Standard Room AC
- K3 = Mezanin
- K4 = Standard Room Kipas
- K5 = Standard Room AC
- TH-1 = Tera House
"""

import os
import pandas as pd

CURRENT_DIR = os.path.dirname(os.path.abspath(__file__))
DS_DIR = os.path.join(CURRENT_DIR, "..")

# Ringkasan Laporan P&L Bulanan Aktual dari Lembar Kas Owner
monthly_pnl_data = [
    {"bulan": "2026-01", "nama_bulan": "Januari", "omzet": 26392000, "biaya_tetap": 3108000, "biaya_variabel": 4397000, "total_biaya": 7505000, "profit_bersih": 18887000, "kamar_terfavorit": "K2", "kota_terbanyak": "Serang"},
    {"bulan": "2026-02", "nama_bulan": "Februari", "omzet": 19461000, "biaya_tetap": 1808000, "biaya_variabel": 325000, "total_biaya": 2133000, "profit_bersih": 17328000, "kamar_terfavorit": "K2", "kota_terbanyak": "Serang"},
    {"bulan": "2026-03", "nama_bulan": "Maret", "omzet": 33700000, "biaya_tetap": 2558000, "biaya_variabel": 1837000, "total_biaya": 4395000, "profit_bersih": 29305000, "kamar_terfavorit": "K1", "kota_terbanyak": "Serang"},
    {"bulan": "2026-04", "nama_bulan": "April", "omzet": 30280000, "biaya_tetap": 2558000, "biaya_variabel": 701000, "total_biaya": 3259000, "profit_bersih": 27021000, "kamar_terfavorit": "K2", "kota_terbanyak": "Tangerang"},
    {"bulan": "2026-05", "nama_bulan": "Mei", "omzet": 32349000, "biaya_tetap": 2558000, "biaya_variabel": 2301000, "total_biaya": 4859000, "profit_bersih": 27490000, "kamar_terfavorit": "K2", "kota_terbanyak": "Serang"},
    {"bulan": "2026-06", "nama_bulan": "Juni", "omzet": 27025000, "biaya_tetap": 2558000, "biaya_variabel": 1963000, "total_biaya": 4521000, "profit_bersih": 22504000, "kamar_terfavorit": "K1", "kota_terbanyak": "Tangerang"},
    {"bulan": "2026-07", "nama_bulan": "Juli", "omzet": 27030000, "biaya_tetap": 3058000, "biaya_variabel": 3693110, "total_biaya": 6751110, "profit_bersih": 20278890, "kamar_terfavorit": "K1", "kota_terbanyak": "Serang"},
    {"bulan": "2026-08", "nama_bulan": "Agustus", "omzet": 33605000, "biaya_tetap": 3058000, "biaya_variabel": 4101210, "total_biaya": 7159210, "profit_bersih": 26445790, "kamar_terfavorit": "K1", "kota_terbanyak": "Serang"},
    {"bulan": "2026-09", "nama_bulan": "September", "omzet": 32112000, "biaya_tetap": 3506000, "biaya_variabel": 3090000, "total_biaya": 6596000, "profit_bersih": 25516000, "kamar_terfavorit": "K1", "kota_terbanyak": "Serang"},
    {"bulan": "2026-10", "nama_bulan": "Oktober", "omzet": 39600000, "biaya_tetap": 3506000, "biaya_variabel": 3441130, "total_biaya": 6947130, "profit_bersih": 32652870, "kamar_terfavorit": "K1", "kota_terbanyak": "Serang"},
    {"bulan": "2026-11", "nama_bulan": "November", "omzet": 15610000, "biaya_tetap": 3506000, "biaya_variabel": 591000, "total_biaya": 3506000, "profit_bersih": 12104000, "kamar_terfavorit": "K1", "kota_terbanyak": "Serang"}
]

df_pnl = pd.DataFrame(monthly_pnl_data)
pnl_csv_path = os.path.join(CURRENT_DIR, "owner_monthly_pnl_clean.csv")
df_pnl.to_csv(pnl_csv_path, index=False)
print(f"Laporan P&L Bulanan tersimpan di: {pnl_csv_path}")
print(f"Total Omzet 11 Bulan  : Rp {df_pnl['omzet'].sum():,}")
print(f"Total Biaya 11 Bulan  : Rp {df_pnl['total_biaya'].sum():,}")
print(f"Total Profit 11 Bulan : Rp {df_pnl['profit_bersih'].sum():,}")
