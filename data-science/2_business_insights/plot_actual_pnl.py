import os
import pandas as pd
import matplotlib.pyplot as plt

CURRENT_DIR = os.path.dirname(os.path.abspath(__file__))
CSV_PATH = os.path.join(CURRENT_DIR, "..", "1_data_cleaning", "owner_monthly_pnl_clean.csv")
CHARTS_DIR = os.path.join(CURRENT_DIR, "charts")
os.makedirs(CHARTS_DIR, exist_ok=True)

df = pd.read_csv(CSV_PATH)

plt.style.use("seaborn-v0_8-whitegrid" if "seaborn-v0_8-whitegrid" in plt.style.available else "default")
fig, ax = plt.subplots(figsize=(10, 5.5), dpi=150)

months = df["nama_bulan"]
omzet = df["omzet"] / 1e6
biaya = df["total_biaya"] / 1e6
profit = df["profit_bersih"] / 1e6

x = range(len(months))
ax.plot(x, omzet, marker="o", color="#1B4332", linewidth=2.5, label="Omzet Bruto (Juta IDR)")
ax.plot(x, profit, marker="s", color="#2D6A4F", linewidth=2.5, linestyle="--", label="Laba Bersih / Net Profit (Juta IDR)")
ax.bar(x, biaya, color="#DDA15E", alpha=0.55, width=0.45, label="Beban Operasional (Juta IDR)")

for i, txt in enumerate(profit):
    ax.annotate(f"{txt:.1f}Jt", (x[i], profit[i] + 0.8), ha="center", fontsize=8.5, fontweight="bold", color="#1B4332")

ax.set_title("Kinerja Keuangan Riil 33HomeStay: Omzet, Biaya & Laba Bersih (Jan-Nov 2026)", fontsize=12, fontweight="bold", pad=15)
ax.set_xticks(x)
ax.set_xticklabels(months, rotation=30)
ax.set_ylabel("Juta Rupiah (IDR)", fontsize=10)
ax.set_ylim(0, 48)
ax.legend(frameon=True, facecolor="white", edgecolor="#CBD5E1")
plt.tight_layout()

out_path = os.path.join(CHARTS_DIR, "05_actual_monthly_pnl_trend.png")
plt.savefig(out_path)
plt.close()
print(f"Chart actual PnL generated successfully: {out_path}")
