"""
Skrip Analisis Data Eksploratif (EDA) & Visualisasi Bisnis 33HomeStay
Penanggung Jawab: Dzakwan Muzakki (Data Science - DS014B6Y0494)
Capstone Project: A26-CS026

Tujuan:
1. Menemukan pola pemesanan weekend vs weekday.
2. Menganalisis kontribusi pendapatan berdasarkan tipe kamar.
3. Mengevaluasi porsi saluran pemesanan (WhatsApp vs OTA vs Walk-in).
4. Menghasilkan grafik visualisasi beresolusi tinggi untuk laporan eksekutif UMKM.
"""

import os
import pandas as pd
import matplotlib.pyplot as plt
import seaborn as sns

CURRENT_DIR = os.path.dirname(os.path.abspath(__file__))
DATA_PATH = os.path.join(CURRENT_DIR, "..", "1_data_cleaning", "clean_reservations.csv")
CHARTS_DIR = os.path.join(CURRENT_DIR, "charts")
os.makedirs(CHARTS_DIR, exist_ok=True)

# Set gaya visual elegan (Palet Forest Green & Warm Sand sesuai tema 33HomeStay)
plt.style.use("seaborn-v0_8-whitegrid" if "seaborn-v0_8-whitegrid" in plt.style.available else "default")
plt.rcParams["font.family"] = "sans-serif"
PRIMARY_COLOR = "#1B4332"
SECONDARY_COLOR = "#2D6A4F"
ACCENT_COLOR = "#DDA15E"
HIGHLIGHT_COLOR = "#BC6C25"

def run_eda():
    print("=" * 65)
    print("MEMULAI ANALISIS DATA EKSPLORATIF (EDA) BISNIS 33HOMESTAY")
    print("=" * 65)

    df = pd.read_csv(DATA_PATH)
    df["check_in"] = pd.to_datetime(df["check_in"])
    df["check_out"] = pd.to_datetime(df["check_out"])
    
    # Filter hanya yang confirmed / paid (bukan batal) untuk analisis finansial
    df_active = df[df["status"] != "CANCELLED"].copy()
    
    # 1. Analisis Weekend vs Weekday
    df_active["day_name"] = df_active["check_in"].dt.day_name()
    df_active["is_weekend"] = df_active["check_in"].dt.dayofweek.isin([4, 5]) # Jumat & Sabtu
    df_active["period_type"] = df_active["is_weekend"].map({True: "Akhir Pekan (Jumat-Sabtu)", False: "Hari Kerja (Minggu-Kamis)"})

    # GRAFIK 1: Okupansi Weekend vs Weekday
    fig, ax = plt.subplots(figsize=(8, 5), dpi=150)
    order = ["Hari Kerja (Minggu-Kamis)", "Akhir Pekan (Jumat-Sabtu)"]
    counts = df_active["period_type"].value_counts().reindex(order)
    percentages = (counts / counts.sum()) * 100
    
    bars = ax.bar(order, counts, color=[SECONDARY_COLOR, PRIMARY_COLOR], width=0.55, edgecolor="#081C15", linewidth=1.2)
    for bar, pct in zip(bars, percentages):
        yval = bar.get_height()
        ax.text(bar.get_x() + bar.get_width() / 2, yval + 1.5, f"{int(yval)} reservasi\n({pct:.1f}%)", ha="center", va="bottom", fontsize=11, fontweight="bold")
    
    ax.set_title("Pola Pemesanan: Hari Kerja vs Akhir Pekan di 33HomeStay", fontsize=13, fontweight="bold", pad=15, color="#081C15")
    ax.set_ylabel("Jumlah Transaksi Reservasi", fontsize=11)
    ax.set_ylim(0, max(counts) * 1.25)
    plt.tight_layout()
    chart1_path = os.path.join(CHARTS_DIR, "01_occupancy_weekend_vs_weekday.png")
    plt.savefig(chart1_path)
    plt.close()
    print(f"[Grafik 1] Berhasil disimpan: {chart1_path}")

    # GRAFIK 2: Pendapatan Berdasarkan Tipe Kamar
    rev_by_room = df_active.groupby("room_type")["total_price"].sum().sort_values(ascending=False)
    fig, ax = plt.subplots(figsize=(9, 5), dpi=150)
    colors = [PRIMARY_COLOR, SECONDARY_COLOR, ACCENT_COLOR, HIGHLIGHT_COLOR]
    
    bars = ax.barh(rev_by_room.index, rev_by_room.values / 1e6, color=colors[:len(rev_by_room)], edgecolor="#081C15")
    for bar in bars:
        wval = bar.get_width()
        ax.text(wval + 0.5, bar.get_y() + bar.get_height() / 2, f"Rp {wval:.1f} Juta", ha="left", va="center", fontsize=10, fontweight="bold")
        
    ax.set_title("Total Kontribusi Pendapatan per Tipe Kamar (Juta Rupiah)", fontsize=13, fontweight="bold", pad=15, color="#081C15")
    ax.set_xlabel("Pendapatan (Juta IDR)", fontsize=11)
    ax.set_xlim(0, max(rev_by_room.values / 1e6) * 1.25)
    ax.invert_yaxis()
    plt.tight_layout()
    chart2_path = os.path.join(CHARTS_DIR, "02_revenue_by_room_type.png")
    plt.savefig(chart2_path)
    plt.close()
    print(f"[Grafik 2] Berhasil disimpan: {chart2_path}")

    # GRAFIK 3: Distribusi Saluran Pemesanan (Channels)
    channel_counts = df_active["channel"].value_counts()
    fig, ax = plt.subplots(figsize=(7, 6), dpi=150)
    wedges, texts, autotexts = ax.pie(
        channel_counts, 
        labels=channel_counts.index, 
        autopct="%1.1f%%", 
        startangle=140,
        colors=["#2D6A4F", "#52B788", "#DDA15E", "#BC6C25"],
        wedgeprops=dict(width=0.6, edgecolor="white", linewidth=2)
    )
    for at in autotexts:
        at.set_fontsize(11)
        at.set_fontweight("bold")
        at.set_color("white")
    ax.set_title("Pangsa Saluran Masuk Pemesanan (Channel Share)", fontsize=13, fontweight="bold", pad=15)
    plt.tight_layout()
    chart3_path = os.path.join(CHARTS_DIR, "03_booking_channel_distribution.png")
    plt.savefig(chart3_path)
    plt.close()
    print(f"[Grafik 3] Berhasil disimpan: {chart3_path}")

    # GRAFIK 4: Asal Kota Tamu
    city_counts = df_active["guest_city"].value_counts().head(6)
    fig, ax = plt.subplots(figsize=(8, 4.5), dpi=150)
    bars = ax.bar(city_counts.index, city_counts.values, color=SECONDARY_COLOR, edgecolor="#081C15", width=0.6)
    for bar in bars:
        yval = bar.get_height()
        ax.text(bar.get_x() + bar.get_width() / 2, yval + 0.8, f"{int(yval)}", ha="center", va="bottom", fontsize=10, fontweight="bold")
    ax.set_title("Demografi Kota Asal Pelanggan Terbanyak", fontsize=13, fontweight="bold", pad=15)
    ax.set_ylabel("Jumlah Tamu", fontsize=11)
    ax.set_ylim(0, max(city_counts.values) * 1.2)
    plt.tight_layout()
    chart4_path = os.path.join(CHARTS_DIR, "04_origin_city_demographics.png")
    plt.savefig(chart4_path)
    plt.close()
    print(f"[Grafik 4] Berhasil disimpan: {chart4_path}")

    print("\nRingkasan Insight Analisis:")
    print(f"- Total Transaksi Sukses: {len(df_active)} reservasi")
    print(f"- Total Omzet Terkumpul: Rp {df_active['total_price'].sum():,}")
    print(f"- Pangsa WhatsApp: {channel_counts.get('Whatsapp', 0) / len(df_active) * 100:.1f}%")
    print(f"- Kamar Pendapatan Tertinggi: {rev_by_room.index[0]} (Rp {rev_by_room.iloc[0]:,})")
    print("=" * 65)

if __name__ == "__main__":
    run_eda()
