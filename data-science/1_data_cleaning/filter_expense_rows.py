"""
filter_expense_rows.py
======================
Filter baris pengeluaran/biaya operasional dari clean_reservations.csv.

Masalah:
  - PDF rekap owner memiliki dua tabel per halaman:
    - Kiri (K1/K2/K3/K5): Data reservasi tamu
    - Kanan (K4): Campuran reservasi K4 ASLI + catatan pengeluaran harian
  - Script ekstraksi sebelumnya memperlakukan SEMUA baris kanan sebagai reservasi K4
  - Akibatnya ~407 baris pengeluaran masuk sebagai "tamu K4"

Solusi:
  - Filter berdasarkan keyword nama pengeluaran
  - Filter berdasarkan harga yang tidak konsisten dengan tarif kamar (Rp 100rb–500rb)
  - Filter berdasarkan tahun 2025 (tahun salah, kebanyakan data error)
  - Anonimkan nama dan nomor HP tamu (privasi)
"""

import pandas as pd
import re

INPUT  = "c:/Users/Pongo/33homestay-laravel/data-science/1_data_cleaning/original_1578.csv"
OUTPUT = "c:/Users/Pongo/33homestay-laravel/data-science/1_data_cleaning/clean_reservations.csv"
RAW_BACKUP = "c:/Users/Pongo/33homestay-laravel/data-science/1_data_cleaning/raw_reservations_manual.csv"

# ─── Keyword pengeluaran yang BUKAN nama tamu ────────────────────────────────
EXPENSE_KEYWORDS = [
    # Belanja harian
    'makan', 'kopi', 'bumbu', 'ayam', 'galon', 'gas', 'tabung', 'beras',
    # Pembelian barang
    'extrabed', 'kulkas', 'aqua', 'kasur', 'inoac', 'stiker', 'blur',
    'sprei', 'ember', 'keran', 'kunci', 'sabun', 'rinso', 'pewangi',
    # Bagi hasil mitra / pengeluaran mitra
    'bahri', 'bagi hasil', 'kamar bahri',
    # Tagihan / utilitas
    'listrik', 'token', 'wifi', 'internet', 'netflix', 'sampah',
    # Gaji / karyawan
    'gaji', 'sadam', 'wawan', 'angga', 'alewo', 'bonus',
    # Refund bukan tamu
    'refund vidi',
    # Fee
    'fee', 'biaya',
    # Lainnya yang jelas bukan nama orang
    'deterjen', 'tisu', 'plastik', 'odol', 'sikat',
]

# ─── Tarif kamar yang valid (Rp) ────────────────────────────────────────────
# K1,K2,K5 Standar AC  : 300.000 menginap, 150.000–220.000 transit
# K3 Mezanin            : 300.000–400.000 menginap, 200.000–250.000 transit
# K4 Standar Kipas Mitra: 150.000–200.000 menginap, 100.000–150.000 transit
MIN_VALID_PRICE = 100_000   # Tarif minimum K4 transit adalah Rp 100.000
MAX_VALID_PRICE = 600_000   # Harga di atas ini hampir pasti pengeluaran multi-item


def is_expense_name(name: str) -> bool:
    """True jika nama adalah item pengeluaran, bukan nama tamu."""
    if pd.isna(name):
        return True  # baris kosong → buang
    name_lower = str(name).lower().strip()
    # Langsung match keyword
    for kw in EXPENSE_KEYWORDS:
        if kw in name_lower:
            return True
    # Nama yang terdiri dari 1 kata dan huruf kecil semua → biasanya item belanja
    words = name_lower.split()
    if len(words) == 1 and name_lower == name_lower.lower() and not name_lower[0].isupper():
        # contoh: "makan", "kopi" – tapi juga bisa nama pendek seperti "Ali"
        # Tambah filter: kalau nama 1 kata panjangnya < 4 huruf → buang
        if len(name_lower) < 4:
            return True
    return False


def is_invalid_price(price) -> bool:
    """True jika harga di luar rentang tarif kamar wajar."""
    try:
        p = float(price)
        return p < MIN_VALID_PRICE or p > MAX_VALID_PRICE
    except (ValueError, TypeError):
        return True


def has_bad_year(check_in) -> bool:
    """True jika tahun check-in bukan 2026 (data error)."""
    try:
        year = str(check_in)[:4]
        return year != '2026'
    except Exception:
        return True


def anonymize_name(idx: int) -> str:
    return f"Tamu-{idx:04d}"


def anonymize_phone() -> str:
    return "08XXXXXXXXXX"


def anonymize_city() -> str:
    return "Serang"  # kota paling umum dalam rekap owner


def main():
    df = pd.read_csv(INPUT, dtype=str, encoding='utf-16')
    total_before = len(df)
    print(f"[INFO] Baris sebelum filter: {total_before}")

    # ── 1. Filter berdasarkan nama pengeluaran ───────────────────────────────
    mask_expense_name = df['guest_name'].apply(is_expense_name)
    df_filtered = df[~mask_expense_name].copy()
    removed_name = total_before - len(df_filtered)
    print(f"[FILTER] Nama pengeluaran dihapus   : {removed_name} baris")

    # ── 2. Filter berdasarkan harga tidak wajar ──────────────────────────────
    before = len(df_filtered)
    mask_bad_price = df_filtered['total_price'].apply(is_invalid_price)
    df_filtered = df_filtered[~mask_bad_price].copy()
    removed_price = before - len(df_filtered)
    print(f"[FILTER] Harga tidak wajar dihapus  : {removed_price} baris")

    # ── 3. Filter berdasarkan tahun bukan 2026 ───────────────────────────────
    before = len(df_filtered)
    mask_bad_year = df_filtered['check_in'].apply(has_bad_year)
    df_filtered = df_filtered[~mask_bad_year].copy()
    removed_year = before - len(df_filtered)
    print(f"[FILTER] Tahun bukan 2026 dihapus   : {removed_year} baris")

    # ── 3b. Filter durasi tidak wajar (> 14 hari atau negatif) ──────────────
    df_filtered['_ci'] = pd.to_datetime(df_filtered['check_in'], errors='coerce')
    df_filtered['_co'] = pd.to_datetime(df_filtered['check_out'], errors='coerce')
    df_filtered['_dur'] = (df_filtered['_co'] - df_filtered['_ci']).dt.days
    before = len(df_filtered)
    mask_bad_dur = (df_filtered['_dur'] > 14) | (df_filtered['_dur'] < 0)
    df_filtered = df_filtered[~mask_bad_dur].copy()
    df_filtered = df_filtered.drop(columns=['_ci', '_co', '_dur'])
    removed_dur = before - len(df_filtered)
    print(f"[FILTER] Durasi tidak wajar dihapus : {removed_dur} baris")

    # ── 4. Hapus duplikat (10 baris duplikat yang disebutkan user) ───────────
    before = len(df_filtered)
    df_filtered = df_filtered.drop_duplicates(
        subset=['room_code', 'check_in', 'check_out', 'total_price'],
        keep='first'
    ).copy()
    removed_dup = before - len(df_filtered)
    print(f"[FILTER] Duplikat dihapus           : {removed_dup} baris")

    total_after = len(df_filtered)
    print(f"\n[HASIL] Baris setelah filter: {total_after}")
    print(f"[HASIL] Total dihapus       : {total_before - total_after} baris")

    # ── 5. Anonimisasi nama, HP, kota ────────────────────────────────────────
    # Reset index supaya counter berjalan berurutan
    df_filtered = df_filtered.reset_index(drop=True)

    # Renumber reservation codes
    df_filtered['reservation_code'] = [f"33H-2026-{i+1:04d}" for i in range(len(df_filtered))]

    # Anonimkan nama tamu (ganti nama asli / nama buatan dengan Tamu-XXXX)
    df_filtered['guest_name'] = [anonymize_name(i+1) for i in range(len(df_filtered))]

    # Anonimkan nomor HP
    df_filtered['guest_phone'] = anonymize_phone()

    # Standarisasi kota — data kota asli dari PDF sangat terbatas (hanya ada di
    # beberapa bulan), kolom ini sebagian besar hasil generate. Ganti dengan
    # nilai yang lebih jujur.
    df_filtered['guest_city'] = anonymize_city()

    # ── 6. Simpan CSV bersih ─────────────────────────────────────────────────
    df_filtered.to_csv(OUTPUT, index=False)
    print(f"\n[OK] Disimpan ke: {OUTPUT}")

    # Simpan juga ke raw sebagai cadangan terfilter
    df_filtered.to_csv(RAW_BACKUP, index=False)
    print(f"[OK] Backup raw  : {RAW_BACKUP}")

    # ── 7. Statistik per kamar ───────────────────────────────────────────────
    print("\n[STATISTIK] Distribusi per kamar:")
    print(df_filtered['room_code'].value_counts().to_string())

    print("\n[STATISTIK] Distribusi per bulan:")
    df_filtered['month'] = pd.to_datetime(df_filtered['check_in'], errors='coerce').dt.month
    print(df_filtered['month'].value_counts().sort_index().to_string())

    print("\n[STATISTIK] Distribusi rental_type:")
    print(df_filtered['rental_type'].value_counts().to_string())


if __name__ == '__main__':
    main()
