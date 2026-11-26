"""
Skrip Analisis Data Eksploratif (EDA) & Visualisasi Bisnis 33HomeStay
Penanggung Jawab: 
- Dewa Bagus Pradnyana Cahyadi Putra (Data Science - DS014B6Y0434)
- Dzakwan Muzakki (Data Science - DS014B6Y0494)
Capstone Project: A26-CS026

Tujuan (Tugas 10):
1. Memisahkan unit inti (K1-K5 + Unassigned) dan TH1.
2. Analisis utama (okupansi, tren, hari kerja vs akhir pekan, demografi kota) dilakukan pada unit inti.
3. Menyajikan ringkasan tersendiri untuk unit Teras House (TH1).
4. Memperbarui grafik visualisasi beresolusi tinggi di folder charts/.
"""

import os
import pandas as pd
import numpy as np
import matplotlib.pyplot as plt

CURRENT_DIR = os.path.dirname(os.path.abspath(__file__))
DATA_PATH = os.path.join(CURRENT_DIR, "..", "1_data_cleaning", "clean_reservations.csv")
CORE_DATA_PATH = os.path.join(CURRENT_DIR, "..", "1_data_cleaning", "clean_reservations_core.csv")
PNL_PATH = os.path.join(CURRENT_DIR, "..", "1_data_cleaning", "owner_monthly_pnl_clean.csv")
CHARTS_DIR = os.path.join(CURRENT_DIR, "charts")
os.makedirs(CHARTS_DIR, exist_ok=True)

# Visual styling
plt.style.use("seaborn-v0_8-whitegrid" if "seaborn-v0_8-whitegrid" in plt.style.available else "default")
plt.rcParams["font.family"] = "sans-serif"
PRIMARY_COLOR = "#1B4332"
SECONDARY_COLOR = "#2D6A4F"
ACCENT_COLOR = "#DDA15E"
HIGHLIGHT_COLOR = "#BC6C25"
MUTED_COLOR = "#6B7280"

def run_eda():
    print("=" * 70)
    print("MENJALANKAN ULANG ANALISIS DATA EKSPLORATIF (EDA) BISNIS 33HOMESTAY")
    print("=" * 70)

    df_all = pd.read_csv(DATA_PATH)
    df_core = pd.read_csv(CORE_DATA_PATH)
    df_th1 = df_all[df_all["unit_scope"] == "teras_house"].copy()

    # Filter unit inti aktif dengan tanggal valid
    df_core_valid = df_core[~df_core["date_unresolved"].fillna(False)].copy()
    df_core_valid["dt_in"] = pd.to_datetime(df_core_valid["check_in"])
    df_core_valid["dt_out"] = pd.to_datetime(df_core_valid["check_out"])

    # ----------------------------------------------------
    # GRAFIK 1: Pola Pemesanan Hari Kerja vs Akhir Pekan (Unit Inti)
    # ----------------------------------------------------
    df_core_valid["day_name"] = df_core_valid["dt_in"].dt.day_name()
    # Jumat & Sabtu dihitung sebagai Akhir Pekan
    df_core_valid["is_weekend"] = df_core_valid["dt_in"].dt.dayofweek.isin([4, 5])
    df_core_valid["period_type"] = df_core_valid["is_weekend"].map({
        True: "Akhir Pekan (Jumat-Sabtu)",
        False: "Hari Kerja (Minggu-Kamis)"
    })

    fig, ax = plt.subplots(figsize=(8, 5), dpi=150)
    order = ["Hari Kerja (Minggu-Kamis)", "Akhir Pekan (Jumat-Sabtu)"]
    counts = df_core_valid["period_type"].value_counts().reindex(order)
    percentages = (counts / counts.sum()) * 100

    bars = ax.bar(order, counts, color=[SECONDARY_COLOR, PRIMARY_COLOR], width=0.52, edgecolor="#081C15", linewidth=1.2)
    for bar, pct in zip(bars, percentages):
        yval = bar.get_height()
        ax.text(bar.get_x() + bar.get_width() / 2, yval + 10, f"{int(yval)} reservasi\n({pct:.1f}%)", 
                ha="center", va="bottom", fontsize=11, fontweight="bold")

    ax.set_title("Pola Pemesanan: Hari Kerja vs Akhir Pekan (Unit Inti 33HomeStay)", fontsize=13, fontweight="bold", pad=15, color="#081C15")
    ax.set_ylabel("Jumlah Transaksi Reservasi", fontsize=11)
    ax.set_ylim(0, max(counts) * 1.25)
    plt.tight_layout()
    chart1_path = os.path.join(CHARTS_DIR, "01_occupancy_weekend_vs_weekday.png")
    plt.savefig(chart1_path)
    plt.close()
    print(f"[Grafik 1] Berhasil diperbarui: {chart1_path}")

    # ----------------------------------------------------
    # GRAFIK 2: Pendapatan Berdasarkan Tipe Kamar & TH1 Callout
    # ----------------------------------------------------
    rev_by_room = df_core.groupby("room_code")["total_price"].sum().sort_values(ascending=False)
    
    fig, ax = plt.subplots(figsize=(9, 5.5), dpi=150)
    colors = [PRIMARY_COLOR, SECONDARY_COLOR, "#386641", ACCENT_COLOR, HIGHLIGHT_COLOR, MUTED_COLOR]
    
    y_labels = [f"{code} ({df_core[df_core['room_code']==code]['room_type'].iloc[0]})" for code in rev_by_room.index]
    bars = ax.barh(y_labels, rev_by_room.values / 1e6, color=colors[:len(rev_by_room)], edgecolor="#081C15")
    for bar in bars:
        wval = bar.get_width()
        ax.text(wval + 1.0, bar.get_y() + bar.get_height() / 2, f"Rp {wval:.1f} Jt", ha="left", va="center", fontsize=10, fontweight="bold")

    ax.set_title("Kontribusi Pendapatan per Kamar Unit Inti (Total: Rp 273,4 Jt)", fontsize=13, fontweight="bold", pad=15, color="#081C15")
    ax.set_xlabel("Pendapatan (Juta IDR)", fontsize=11)
    ax.set_xlim(0, max(rev_by_room.values / 1e6) * 1.22)
    ax.invert_yaxis()
    
    # Tambahkan catatan ringkasan TH1
    th1_rev = df_th1["total_price"].sum() / 1e6
    th1_trx = len(df_th1)
    ax.text(0.98, 0.05, f"Catatan Scope Unit Luar:\nTeras House (TH1): Rp {th1_rev:.2f} Jt ({th1_trx} trx)\n(Dikeluarkan dari scope sistem inti)",
            transform=ax.transAxes, ha="right", va="bottom", fontsize=9.5,
            bbox=dict(boxstyle="round,pad=0.5", facecolor="#FEF3C7", edgecolor="#D97706", alpha=0.9))

    plt.tight_layout()
    chart2_path = os.path.join(CHARTS_DIR, "02_revenue_by_room_type.png")
    plt.savefig(chart2_path)
    plt.close()
    print(f"[Grafik 2] Berhasil diperbarui: {chart2_path}")

    # ----------------------------------------------------
    # GRAFIK 3: Distribusi Saluran Pemesanan (Channels & OTA)
    # ----------------------------------------------------
    channel_counts = df_core["channel"].value_counts()
    ota_breakdown = df_core[df_core["channel"] == "OTA"]["ota_name"].value_counts()

    fig, (ax1, ax2) = plt.subplots(1, 2, figsize=(11, 5), dpi=150, gridspec_kw={'width_ratios': [1.2, 1]})
    
    # Pie chart saluran utama
    ax1.pie(
        channel_counts, 
        labels=[f"{idx}\n({val} trx)" for idx, val in channel_counts.items()], 
        autopct="%1.1f%%", 
        startangle=140,
        colors=["#2D6A4F", ACCENT_COLOR],
        wedgeprops=dict(width=0.6, edgecolor="white", linewidth=2)
    )
    ax1.set_title("Pangsa Saluran Masuk (Core Units)", fontsize=12, fontweight="bold", pad=10)

    # Bar chart rincian OTA
    if len(ota_breakdown) > 0:
        bars_ota = ax2.bar(ota_breakdown.index, ota_breakdown.values, color=PRIMARY_COLOR, edgecolor="#081C15", width=0.5)
        for b in bars_ota:
            h = b.get_height()
            ax2.text(b.get_x() + b.get_width()/2, h + 0.1, f"{int(h)}", ha="center", va="bottom", fontsize=10, fontweight="bold")
        ax2.set_title("Rincian Platform OTA Tercatat (13 Trx)", fontsize=12, fontweight="bold", pad=10)
        ax2.set_ylabel("Jumlah Transaksi", fontsize=10)
        ax2.set_ylim(0, max(ota_breakdown.values) + 1.5)
        ax2.tick_params(axis='x', rotation=25)

    plt.tight_layout()
    chart3_path = os.path.join(CHARTS_DIR, "03_booking_channel_distribution.png")
    plt.savefig(chart3_path)
    plt.close()
    print(f"[Grafik 3] Berhasil diperbarui: {chart3_path}")

    # ----------------------------------------------------
    # GRAFIK 4: Demografi Kota Asal Tamu (Recorded vs Imputed)
    # ----------------------------------------------------
    city_counts = df_core["guest_city"].value_counts().head(7)
    fig, ax = plt.subplots(figsize=(8.5, 4.8), dpi=150)
    bars = ax.bar(city_counts.index, city_counts.values, color=SECONDARY_COLOR, edgecolor="#081C15", width=0.58)
    for bar in bars:
        yval = bar.get_height()
        ax.text(bar.get_x() + bar.get_width() / 2, yval + 10, f"{int(yval)}", ha="center", va="bottom", fontsize=10, fontweight="bold")
    ax.set_title("Demografi Kota Asal Pelanggan Terbanyak (Unit Inti)", fontsize=13, fontweight="bold", pad=15)
    ax.set_ylabel("Jumlah Tamu Terdata", fontsize=11)
    ax.set_ylim(0, max(city_counts.values) * 1.2)
    
    # Sub-note provenance modus
    ax.text(0.98, 0.90, "Catatan Provenance:\nKota kosong diisi modus 'Serang' (K-1)\nTotal recorded: 41,1% | Imputed: 58,9%",
            transform=ax.transAxes, ha="right", va="top", fontsize=9,
            bbox=dict(boxstyle="round,pad=0.4", facecolor="#F3F4F6", edgecolor="#CBD5E1"))

    plt.tight_layout()
    chart4_path = os.path.join(CHARTS_DIR, "04_origin_city_demographics.png")
    plt.savefig(chart4_path)
    plt.close()
    print(f"[Grafik 4] Berhasil diperbarui: {chart4_path}")

    # ----------------------------------------------------
    # GRAFIK 5: Kinerja Keuangan Riil Bulanan (PnL)
    # ----------------------------------------------------
    if os.path.exists(PNL_PATH):
        df_pnl = pd.read_csv(PNL_PATH)
        fig, ax = plt.subplots(figsize=(10, 5.5), dpi=150)
        months = df_pnl["nama_bulan"]
        omzet = df_pnl["omzet"] / 1e6
        biaya = df_pnl["total_biaya"] / 1e6
        profit = df_pnl["profit_bersih"] / 1e6

        x = range(len(months))
        ax.plot(x, omzet, marker="o", color=PRIMARY_COLOR, linewidth=2.5, label="Omzet Bruto (Juta IDR)")
        ax.plot(x, profit, marker="s", color=SECONDARY_COLOR, linewidth=2.5, linestyle="--", label="Laba Bersih (Juta IDR)")
        ax.bar(x, biaya, color=ACCENT_COLOR, alpha=0.6, width=0.45, label="Beban Operasional (Juta IDR)")

        for i, txt in enumerate(profit):
            ax.annotate(f"{txt:.1f}Jt", (x[i], profit[i] + 0.8), ha="center", fontsize=8.5, fontweight="bold", color=PRIMARY_COLOR)

        ax.set_title("Kinerja Keuangan Riil 33HomeStay: Omzet, Biaya & Laba Bersih (Jan-Nov 2026)", fontsize=12, fontweight="bold", pad=15)
        ax.set_xticks(x)
        ax.set_xticklabels(months, rotation=30)
        ax.set_ylabel("Juta Rupiah (IDR)", fontsize=10)
        ax.set_ylim(0, 48)
        ax.legend(frameon=True, facecolor="white", edgecolor="#CBD5E1")
        plt.tight_layout()

        chart5_path = os.path.join(CHARTS_DIR, "05_actual_monthly_pnl_trend.png")
        plt.savefig(chart5_path)
        plt.close()
        print(f"[Grafik 5] Berhasil diperbarui: {chart5_path}")

    print("\n" + "=" * 70)
    print("RINGKASAN METRIK BISNIS TUGAS 10:")
    print(f"1. UNIT INTI (K1-K5 + Unassigned):")
    print(f"   - Jumlah Reservasi : {len(df_core)} baris")
    print(f"   - Total Omzet      : Rp {df_core['total_price'].sum():,}")
    print(f"   - Rata-rata/Trx    : Rp {df_core['total_price'].mean():,.0f}")
    print(f"   - Sewa Harian      : {(df_core['rental_type']=='daily').sum()} baris ({(df_core['rental_type']=='daily').mean()*100:.1f}%)")
    print(f"   - Sewa Transit     : {(df_core['rental_type']=='transit').sum()} baris ({(df_core['rental_type']=='transit').mean()*100:.1f}%)")
    print(f"\n2. TERAS HOUSE (TH1 - Scope Luar):")
    print(f"   - Jumlah Reservasi : {len(df_th1)} baris")
    print(f"   - Total Omzet      : Rp {df_th1['total_price'].sum():,}")
    print(f"   - Rata-rata/Trx    : Rp {df_th1['total_price'].mean():,.0f}")
    print("=" * 70)

if __name__ == "__main__":
    run_eda()
