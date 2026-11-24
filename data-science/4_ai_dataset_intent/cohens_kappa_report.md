# Laporan Inter-Annotator Agreement (Cohen's Kappa)

* **Nama Proyek:** Centralized Reservation System 33HomeStay Anyer (A26-CS026)
* **Penanggung Jawab:** Dzakwan Muzakki (Data Science - DS014B6Y0494)
* **Anotator 1:** Dzakwan Muzakki (Data Science)
* **Anotator 2:** Dewa Bagus Pradnyaan Cahyadi Putra (Data Science)
* **Kategori Deliverable:** Tambahan / Opsional (Penyediaan & Formatisasi Data untuk Model AI)
* **Metrik Kesepakatan:** Cohen's Kappa ($\kappa$)

---

## 1. Ringkasan Hasil Uji Reliabilitas Anotasi
Pengujian kesepakatan antar-anotator (*inter-annotator agreement*) dilakukan secara independen terhadap **50 sampel pesan pelanggan** secara *blind evaluation* sesuai dengan [Pedoman Pelabelan (Annotation Guidelines)](annotation_guidelines.md).

* **Jumlah Sampel Uji:** 50 pesan teks WhatsApp
* **Observed Agreement ($P_o$):** **94.0%** (47 dari 50 pesan disepakati identik)
* **Skor Cohen's Kappa ($\kappa$):** **0.9245**
* **Tingkat Reliabilitas (Landis & Koch, 1977):** **Almost Perfect Agreement (Kesepakatan Hampir Sempurna)**

---

## 2. Matriks Kontingensi / Confusion Matrix Antar-Anotator

| Anotator 1 \ Anotator 2 | reservasi | cek_ketersediaan | tanya_info | hubungi_admin | lainnya | Total |
|:---|:---:|:---:|:---:|:---:|:---:|:---:|
| **reservasi** | 11 | 0 | 0 | 0 | 0 | 11 |
| **cek_ketersediaan** | 1 | 7 | 0 | 0 | 0 | 8 |
| **tanya_info** | 0 | 1 | 11 | 0 | 0 | 12 |
| **hubungi_admin** | 0 | 0 | 1 | 10 | 0 | 11 |
| **lainnya** | 0 | 0 | 0 | 0 | 8 | 8 |

---

## 3. Analisis Kasus Disparitas (Disagreements)
Dari 50 sampel yang diuji, terdapat 3 kasus perbedaan penafsiran:
1. **Pesan:** *"Berapa tarif kamar AC kalau sewa pas malam minggu besok?"*
   * *Anotator 1:* `tanya_info` (karena menanyakan tarif sewa).
   * *Anotator 2:* `cek_ketersediaan` (karena menanyakan ketersediaan malam minggu).
   * *Resolusi:* Berdasarkan pedoman pasal 2.3, jika menanyakan tarif spesifik tanggal tertentu, diklasifikasikan ke `cek_ketersediaan`.
2. **Pesan:** *"Malam ini ada kamar kosong ready check in sekarang?"*
   * *Anotator 1:* `cek_ketersediaan` (menanyakan status kamar).
   * *Anotator 2:* `reservasi` (niat langsung menginap saat ini).
   * *Resolusi:* Tetap `cek_ketersediaan` sampai ada konfirmasi pemesanan nama tamu.
3. **Pesan:** *"Ada nomor CS atau resepsionis yang standby 24 jam?"*
   * *Anotator 1:* `hubungi_admin` (meminta nomor staf langsung).
   * *Anotator 2:* `tanya_info` (menanyakan informasi ketersediaan resepsionis).
   * *Resolusi:* Diklasifikasikan ke `hubungi_admin` karena indikasi kebutuhan komunikasi dengan manusia.

---

## 4. Kesimpulan Kesiapan Dataset AI
Nilai Cohen's Kappa sebesar **0.9245 (>= 0.81)** membuktikan bahwa dataset intent pesan pelanggan memiliki konsistensi semantik yang sangat teruji, bebas dari subjektivitas ekstrem, dan sangat layak digunakan untuk proses fine-tuning maupun inferensi pada model AI Chatbot tim Capstone A26-CS026.
