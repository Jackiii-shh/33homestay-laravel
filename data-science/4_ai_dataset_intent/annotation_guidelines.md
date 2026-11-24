# Pedoman Pelabelan (Annotation Guidelines) - Klasifikasi Intent Pesan 33HomeStay

* **Nama Proyek:** Centralized Reservation System 33HomeStay Anyer
* **Kode Proyek:** A26-CS026
* **Penanggung Jawab:** Dzakwan Muzakki (Data Science - DS014B6Y0494)
* **Kategori Deliverable:** Tambahan / Opsional (Penyediaan & Formatisasi Data untuk Model AI)
* **Target Model AI:** NLP Intent Classifier Chatbot Asisten Virtual WhatsApp

---

## 1. Tujuan Pelabelan
Pedoman ini disusun sebagai acuan formal bagi seluruh anotator (Data Science & AI Engineer) dalam memberikan label kategori maksud (*intent*) pada setiap pesan teks yang dikirimkan oleh calon pelanggan 33HomeStay melalui saluran WhatsApp atau widget website.

---

## 2. Taksonomi Kelas Intent (5 Kelas)

### Kelas 1: `reservasi`
* **Definisi:** Pesan pelanggan yang menyatakan niat tegas (*explicit booking intent*) untuk menyewa/memesan unit kamar tertentu pada kurun waktu tertentu, meminta nomor rekening pembayaran DP, atau mengonfirmasi pengambilan kamar.
* **Kata Kunci Indikator:** *booking, pesan kamar, sewa, ambil kamar, keep kamar, kunci kamar, fix ambil, transfer DP, atas nama.*
* **Contoh Positif:**
  * *"Halo admin, saya mau booking kamar Standard AC untuk tanggal 15 Agustus ya"*
  * *"Saya fix ambil Tera House ya kak buat tgl 12-14 September, mohon kirim nomor rekening"*
* **Batas Pembeda (Disambiguasi):** Jika pesan hanya bertanya apakah kamar kosong tanpa menyatakan ingin memesan sekarang, masukkan ke `cek_ketersediaan`.

---

### Kelas 2: `cek_ketersediaan`
* **Definisi:** Pesan pelanggan yang menanyakan status okupansi atau ketersediaan kamar pada tanggal/periode tertentu (*availability inquiry*) sebelum memutuskan memesan.
* **Kata Kunci Indikator:** *ada yang kosong, available, ready gak, masih ada slot, sudah full booked, tanggal x kosong gak.*
* **Contoh Positif:**
  * *"Halo kak, apakah tanggal 17 Agustus kamar AC masih ada yang kosong?"*
  * *"Tera House ready gak untuk tanggal 25-27 September?"*
* **Batas Pembeda (Disambiguasi):** Jika pelanggan menyebutkan tanggal dan menyatakan *"saya mau pesan kamar ini tgl x"*, masukkan ke `reservasi`.

---

### Kelas 3: `tanya_info`
* **Definisi:** Pesan pelanggan yang menanyakan informasi umum properti, rincian fasilitas kamar, harga tarif per malam, jarak ke pantai, jam check-in/check-out, ketersediaan WiFi, dan peraturan homestay (*general FAQ*).
* **Kata Kunci Indikator:** *fasilitas, harga, tarif berapa, lokasi sebelah mana, jam check-in, kolam renang, foto kamar, air panas, wifi.*
* **Contoh Positif:**
  * *"Fasilitas di kamar Mezanin dapat apa saja ya kak?"*
  * *"Jarak dari 33HomeStay ke pantai pasir putih Anyer berapa menit?"*
* **Batas Pembeda (Disambiguasi):** Jika bertanya harga sekaligus menanyakan apakah kamar tersebut kosong di tanggal tertentu, prioritaskan `cek_ketersediaan`.

---

### Kelas 4: `hubungi_admin`
* **Definisi:** Pesan pelanggan yang secara eksplisit meminta kontak langsung staf manusia / WhatsApp pengelola, ingin dihubungi via telepon, atau meminta eskalasi masalah ke penanggung jawab homestay.
* **Kata Kunci Indikator:** *nomor whatsapp pengelola, bicara langsung, telepon saya, nomor admin, kontak owner, resepsionis, staf lapangan.*
* **Contoh Positif:**
  * *"Boleh minta nomor telepon WhatsApp pengelola yang bisa dihubungi?"*
  * *"Admin tolong telepon saya ya, nomor ini penting mau konfirmasi"*
* **Batas Pembeda (Disambiguasi):** Pertanyaan biasa yang dijawab oleh chatbot tidak masuk ke kelas ini, kecuali jika tamu secara spesifik meminta manusia (*human handoff*).

---

### Kelas 5: `lainnya`
* **Definisi:** Pesan percakapan pembuka, penutup, ucapan salam (*greeting*), ucapan terima kasih, pesan konfirmasi non-transaksional, atau pesan pengujian.
* **Kata Kunci Indikator:** *selamat pagi, terima kasih, makasih infonya, halo, assalamualaikum, siap, oke, tes.*
* **Contoh Positif:**
  * *"Terima kasih banyak atas informasinya ya min, sangat membantu"*
  * *"Selamat pagi kak, salam kenal"*

---

## 3. Protokol Verifikasi Anotasi Ganda & Inter-Annotator Agreement
Untuk memastikan objektivitas pelabelan dan mencegah bias individu:
1. Anotator Utama (Annotator 1): **Dzakwan Muzakki (DS)**.
2. Anotator Pembanding (Annotator 2): **Dewa Bagus P. C. P. (DS)** atau **Susan Jong (AE)**.
3. Sampel acak sebanyak 50 pesan independen diperiksa silang secara *blind annotation* (tanpa saling melihat hasil anotasi rekan).
4. Tingkat kesepakatan dihitung menggunakan statistik **Cohen's Kappa ($\kappa$)**.
5. Nilai $\kappa \ge 0.81$ dikategorikan sebagai *Almost Perfect Agreement* (Konsistensi Sangat Tinggi) dan memenuhi syarat untuk dilatihkan ke model NLP.
