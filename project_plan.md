# Asah 2026 Capstone Project — Project Plan

**Asah 2026** led by Dicoding | supported by GoTo

| Item | Detail |
|------|--------|
| **ID Tim Capstone Project** | A26-CS026 |
| **Nama UMKM** | 33HomeStay |
| **Bidang UMKM** | Pariwisata (Akomodasi Penginapan) |
| **Judul Use Case** | Sistem Reservasi Terpusat dengan Validasi Bentrok Kamar untuk Mencegah Double Booking pada 33HomeStay |
| **Tautan Dokumen Use Case** | [Asah 2026] Template Interview dan Use Case UMKM |

## Daftar Anggota Tim

| No | ID | Nama | Learning Path | Status |
|----|----|------|---------------|--------|
| 1 | DS014B6Y0434 | Dewa Bagus Pradnyaan Cahyadi Putra | Data Science | Aktif |
| 2 | DS014B6Y0494 | Dzakwan Muzakki | Data Science | Aktif |
| 3 | FS317B6X0913 | Lia Kartika | Full-Stack | Aktif |
| 4 | FS288B6Y1802 | Yuslani | Full-Stack | Aktif |
| 5 | AE319B6X1687 | Susan Jong | AI Engineer | Aktif |
| 6 | AE208B6X0860 | Kaisya Anindya Callista Putri Kusyanto | AI Engineer | Aktif |

---

## A. Ringkasan Eksekutif

33HomeStay adalah UMKM akomodasi penginapan dengan **empat tipe kamar** (Standard Room AC, Standard Room Kipas, Mezanin, dan Teras House — total **6 unit**) untuk keluarga, pasangan, dan grup kecil.

**Masalah saat ini:**
- Pencatatan reservasi masih manual di kertas dan direkap seminggu sekali.
- Pesanan masuk lewat WhatsApp Business dan platform OTA (Agoda, Tiket.com) yang belum terhubung.
- Akibatnya sering terjadi **double booking** (pemesanan ganda pada kamar dan tanggal yang sama), terutama saat akhir pekan dan hari libur.
- Dampak: pembatalan sepihak, keluhan pelanggan, respons WhatsApp yang lambat, serta risiko ketidaksesuaian pencatatan transaksi.

**Solusi yang dibangun:**
Tim ASAH ASIH ASUH membangun **MVP aplikasi web** yang nyaman dipakai di ponsel.

**Pengguna:**
- **Pengelola** (Admin Sosmed dan Admin Lapangan) → operasional
- **Owner** → memantau dan mengaudit

**Fitur utama:**
- Pencatatan reservasi terpusat
- Validasi bentrok otomatis berdasarkan unit kamar dan rentang tanggal menginap
- Kalender ketersediaan terpadu
- Pencatatan DP dan pelunasan dengan **invoice PDF otomatis** yang dapat diverifikasi
- Dasbor pemantauan Owner

**Catatan penting:**
- Calon tamu **tidak memesan sendiri**.
- Website publik hanya menampilkan informasi ketersediaan dan **chatbot AI** yang mengarahkan tamu ke WhatsApp Business Pengelola.
- Ketersediaan tetap dikonfirmasi manusia dan seluruh data tercatat di sistem.
- **Smart WhatsApp Extractor** membantu Pengelola mengisi form reservasi dari pesan WhatsApp untuk diperiksa sebelum disimpan.

**Manfaat yang diharapkan:**
- Berkurangnya kasus double booking
- Konfirmasi reservasi lebih cepat
- Penghematan waktu pencatatan berulang
- Transparansi nominal pembayaran
- Wawasan data (pola okupansi dan indikator bisnis) untuk keputusan Owner

**Durasi proyek:** 4 minggu  
- 3 minggu mengembangkan dan menghubungkan fitur utama  
- 1 minggu pengujian, validasi bersama UMKM, dan dokumentasi

---

## B. Cakupan Proyek dan Hasil Kerja

### Alur Proses Reservasi (Activity Diagram)

1. Calon customer membuka website 33HomeStay atau menemukan info lewat media sosial, Google Maps, atau OTA.
2. Mengecek ketersediaan berdasarkan tanggal dan tipe kamar atau bertanya kepada chatbot (hasilnya hanya informasi).
3. Website atau chatbot mengarahkan customer ke WhatsApp Business Pengelola dengan pesan yang sudah terisi.
4. Pengelola mengecek ketersediaan dan meng-input data reservasi ke aplikasi web (form dapat terisi otomatis dari pesan WhatsApp lewat Smart WhatsApp Extractor, lalu Pengelola memeriksa dan menyimpan).
5. Sistem memvalidasi bentrok jadwal secara otomatis. Bila aman, reservasi tersimpan berstatus **Menunggu DP** dan tanggal kamar langsung terblokir di kalender.
6. Pengelola memverifikasi bukti pembayaran lalu mencatat DP atau pelunasan.
7. Sistem mengonfirmasi reservasi dan menerbitkan **Invoice Digital PDF**, lalu Pengelola mengirimkannya ke customer lewat WhatsApp.
8. Pada hari-H tamu melakukan check-in setelah lunas (atau reservasi ditandai **No-Show** dan DP hangus bila tamu tidak hadir sampai batas waktu yang disepakati Owner).
9. Sistem memperbarui status kamar menjadi **Dibooking**, **Terisi**, atau **Dibersihkan**.
10. Owner memantau reservasi, pembayaran, dan ketersediaan kamar melalui dashboard.

### Fitur / Hasil Kerja

#### Data Science

| Fitur / Hasil Kerja | Wajib / Tambahan | Indikator Keberhasilan | Penanggung Jawab |
|---------------------|------------------|------------------------|------------------|
| Analisis Peran Data & Perumusan Pertanyaan Bisnis | Wajib | Pertanyaan bisnis terumuskan (tren okupansi kamar, pola akhir pekan vs hari biasa, durasi menginap, kanal pemesanan, frekuensi pembatalan dan no-show) beserta penjelasan peran data dalam solusi, didokumentasikan di README. | Dewa Bagus Pradnyaan Cahyadi Putra (DS) |
| Pengolahan & Pembersihan Data Dummy (Data Wrangling) | Wajib | Data dummy reservasi tersusun rapi sesuai struktur laporan manual 33HomeStay (No, Tanggal Check-in, Nama Tamu, Asal Kota, No. Kamar, Jml per. Malam, Total Harga, Metode Pembayaran, Keterangan), bersih dari duplikat dan nilai hilang, ditandai sintetis, dan siap dimasukkan ke basis data. Seluruh tahapan wrangling terdokumentasi di notebook. | Dzakwan Muzakki (DS) |
| Perancangan Arsitektur & Skema Database | Wajib | Skema database (users, room_types, rooms, reservations, payments, invoices, activity_logs) lengkap dengan relasi dan indeks rentang tanggal sudah terimplementasi lewat migrasi pada aplikasi. ERD dan kamus tabel tercantum di README. | Dewa Bagus Pradnyaan Cahyadi Putra (DS) |
| Analisis Data Eksploratif (EDA) & Logika Validasi Bentrok | Wajib | Pertanyaan bisnis terjawab lewat EDA. Algoritma pendeteksi tumpang tindih rentang tanggal terformulasi (dua reservasi aktif pada unit yang sama bentrok bila check-in A < check-out B dan check-out A > check-in B; check-out dan check-in pada hari yang sama tidak bentrok) dan diuji pada data historis untuk menemukan potensi double booking. | Dewa Bagus Pradnyaan Cahyadi Putra (DS) |
| Penyajian Temuan, Insight, & Rekomendasi Bisnis | Wajib | Dokumen temuan analisis data (pola puncak pemesanan weekend vs weekday) dan rekomendasi operasional untuk UMKM. Seluruh angka bersumber dari hasil analisis. | Dzakwan Muzakki (DS) |
| Pemeriksaan Ketepatan Data (Data Quality Check) | Wajib | Data lolos seluruh aturan pemeriksaan (tanpa duplikat, tanggal logis, tanpa tumpang tindih tanggal pada unit yang sama, total = harga per malam × jumlah malam, nominal pembayaran tidak melebihi tagihan) dan hasil pemeriksaan dilaporkan sebelum data dipakai di sistem akhir. | Dzakwan Muzakki (DS) & Dewa Bagus Pradnyaan Cahyadi Putra (DS) |
| Paket Data & Hasil Analisis untuk Tim Lain | Wajib | CSV bersih tanpa data pribadi, kamus data, dan petunjuk penggunaan tersedia di repositori sehingga dapat dipakai tim Full-Stack dan AI Engineer. | Dzakwan Muzakki (DS) |
| Dasbor Interaktif Pemantauan Bisnis (Embed Streamlit) | Tambahan | Dasbor pemantau indikator bisnis (tingkat okupansi, estimasi pendapatan harian/bulanan, rasio DP/Lunas) dengan filter interaktif dan pembaruan data lewat unggah CSV, disematkan di menu analitik pada dashboard Owner. | Dewa Bagus Pradnyaan Cahyadi Putra (DS) |
| Penyediaan & Formatisasi Data untuk Model AI | Tambahan | Dataset transaksi yang telah ditransformasi, di-feature engineering, dan dibagi (train/test split) secara bersih untuk kebutuhan tim AI. Dataset intent pesan pelanggan memuat 5 kelas (reservasi, cek_ketersediaan, tanya_info, hubungi_admin, lainnya), berlabel sintetis, dengan pedoman pelabelan tertulis, kamus data, dan pembagian latih/validasi/uji per penulis tanpa kebocoran. Sebagian label diperiksa ulang anggota lain dan kesepakatannya dihitung dengan Cohen’s kappa. | Dzakwan Muzakki (DS) |

#### AI Engineer

| Fitur / Hasil Kerja | Wajib / Tambahan | Indikator Keberhasilan | Penanggung Jawab |
|---------------------|------------------|------------------------|------------------|
| Model Klasifikasi Intent Chatbot & Preprocessing Teks | Wajib | Model klasifikasi teks Bahasa Indonesia dengan 5 kelas (reservasi, cek_ketersediaan, tanya_info, hubungi_admin, lainnya) dilatih dari dataset intent tim DS. Preprocessing (huruf kecil, normalisasi singkatan dan bahasa gaul, penyeragaman tanggal, vektorisasi) berada dalam satu pipeline yang dilatih ulang per lipatan, dengan validasi silang bersarang per penulis agar tidak terjadi kebocoran. Ambang keyakinan dipilih dari prediksi validasi, bukan data uji, dan pesan di bawah ambang dialihkan ke admin. Masalah yang diselesaikan model dijelaskan di README. | Susan Jong (AE) & Kaisya Anindya Callista Putri Kusyanto (AE) |
| Perbandingan Model, Hasil Uji, & Dokumentasi Sumber | Wajib | Empat konfigurasi (aturan kata kunci, LinearSVC, Logistic Regression, Random Forest) dibandingkan dengan metrik yang sama (micro-F1, macro-F1 beserta interval kepercayaan 95%, analisis kesalahan (error) akurasi, precision dan recall per kelas). Dilaporkan juga recall eskalasi (porsi pesan urusan admin yang memang berakhir di admin) dan persentase pesan yang dialihkan. Tabel perbandingan, alasan pemilihan model terbaik, hasil uji pada data yang disisihkan, dan sumber setiap data, pustaka, dan model beserta deskripsi di README. | Susan Jong (AE) & Kaisya Anindya Callista Putri Kusyanto (AE) |
| Layanan REST API FastAPI untuk Serving Model | Wajib | Layanan FastAPI dengan endpoint `/api/v1/intent/classify`, `/api/v1/rag/answer`, `/api/v1/booking/extract`, dan `/api/v1/health`, dilindungi header `X-API-Key`, dengan dokumentasi Swagger di `/docs`. Dikembangkan bersama Full-Stack dan dapat diakses online oleh back-end aplikasi. Kontrak endpoint, format request/response, dan kode error (401, 422, 429, 503) disepakati sebelum pengembangan. | Kaisya Anindya Callista Putri Kusyanto (AE) & Lia Kartika (FS) |
| Fitur Generatif AI (Chatbot) via API Model Proprietary | Wajib | Chatbot memakai API model proprietary lewat back-end, dengan prompt terstruktur, penolakan sopan untuk pertanyaan di luar konteks usaha, dan mode cadangan berbasis aturan bila layanan gagal atau kuota habis. Chatbot juga menjawab ketersediaan kamar: tanggal, durasi, dan tipe kamar diambil dari pesan, lalu back-end menghitung unit kosong dari data reservasi (hanya reservasi ber-DP yang menahan kamar, unit termasuk Teras House). Angka berasal dari database, bukan dikarang model, dan jawaban memuat catatan bahwa kepastian dikonfirmasi admin. | Susan Jong (AE) & Kaisya Anindya Callista Putri Kusyanto (AE) |
| Smart WhatsApp Extractor (Form Reservasi Terisi Otomatis) | Tambahan | Pengelola menempelkan pesan WhatsApp pelanggan pada layar Reservasi Baru, lalu API generatif proprietary dengan prompt terstruktur dan keluaran JSON yang mengisi form: nama, asal kota, tanggal dan jam check-in, jam check-out, durasi malam, tipe kamar (empat tipe), jumlah tamu, dan kendaraan. Bagian yang tidak disebut dikosongkan dan ditandai, bukan ditebak. Pengelola memeriksa lalu menyimpan, dan form manual tetap berfungsi bila layanan gagal. Dari 20 sampai 30 obrolan uji dicatat persentase cocok persis per kolom dan tingkat halusinasi di README. | Susan Jong (AE), Lia Kartika (FS) & Yuslani (FS) |
| RAG Chatbot & Uji Pertanyaan di Luar Konteks | Tambahan | Chatbot menjawab pertanyaan info (kamar, harga, kebijakan) dari dokumen usaha memakai model embedding dan vector database (ChromaDB atau FAISS) beserta sumber jawaban. Minimal 10 pertanyaan di luar konteks diuji dan ditolak dengan sopan; hasilnya ditulis di README. | Susan Jong (AE) & Kaisya Anindya Callista Putri Kusyanto (AE) |
| Dokumen Panduan & User Manual | Wajib | Panduan penggunaan singkat bergambar untuk Pengelola dan Owner (alur reservasi hingga cetak invoice) selesai disusun untuk UMKM. | Kaisya Anindya Callista Putri Kusyanto (AE) |

#### Full-Stack

| Fitur / Hasil Kerja | Wajib / Tambahan | Indikator Keberhasilan | Penanggung Jawab |
|---------------------|------------------|------------------------|------------------|
| Logika Validasi Bentrok Reservasi & API | Wajib | Back-end mampu mendeteksi tumpang tindih rentang tanggal pada unit yang sama dan menolak reservasi bentrok dengan pesan jelas disertai alternatif (unit lain, tipe lain, tanggal terdekat). Pemeriksaan dijalankan saat simpan, ubah tanggal, pindah kamar, dan perpanjang inap di dalam transaksi database. | Lia Kartika (FS), Yuslani (FS) & Dewa Bagus Pradnyaan Cahyadi Putra (DS) |
| Tampilan Kalender Ketersediaan | Wajib | Kalender per unit (6 unit kamar) bersumber dari satu database: perubahan oleh satu pengelola langsung terlihat oleh pengelola lain dan Owner pada kalender yang sama, tanpa menunggu rekap mingguan. | Lia Kartika (FS) & Yuslani (FS) |
| Otomatisasi Generator Invoice Digital (PDF) | Wajib | Dokumen PDF invoice terbit otomatis pada setiap pembayaran berhasil, berisi rincian pemesanan, nominal, rincian DP/pelunasan, sisa tagihan, nomor unik, dan tautan verifikasi keaslian. | Lia Kartika (FS) & Yuslani (FS) |
| Website Informasi Publik & Cek Ketersediaan | Wajib | Landing page responsif menampilkan tipe kamar, harga, ketentuan, dan FAQ, serta formulir cek ketersediaan per tanggal, jumlah tamu, dan tipe kamar. Hasil hanya informasi (tanpa pemesanan mandiri) lengkap dengan rincian harga dan total, dan tombol Pesan via WhatsApp mengarahkan ke WhatsApp Business Pengelola. | Lia Kartika (FS) & Yuslani (FS) |
| Antarmuka Web Responsive (Input & Dashboard) | Wajib | Formulir reservasi dan dashboard ketersediaan kamar dapat diakses dengan nyaman melalui layar HP. | Lia Kartika (FS) & Yuslani (FS) |
| Formulir Reservasi Internal (Input oleh Pengelola) | Wajib | Pengelola dapat mengisi data tamu (nama, asal kota, no. HP, jumlah tamu), tanggal menginap, tipe/unit kamar, dan kanal pesanan (WhatsApp, OTA, walk-in). Pemilih kamar hanya menampilkan unit yang kosong, dan input kosong atau tidak valid ditolak dengan pesan jelas. | Lia Kartika (FS) & Yuslani (FS) |
| Halaman Detail Reservasi & Ringkasan Biaya | Wajib | Sistem menampilkan detail reservasi, durasi menginap, rincian harga, total, DP minimum, sisa tagihan yang belum dibayar, dan riwayat aktivitas sehingga pengelola dapat meninjau reservasi sebelum DP dicatat. | Lia Kartika (FS) & Yuslani (FS) |
| Antarmuka Pengelolaan Reservasi | Wajib | Pengelola dapat melihat daftar dan detail reservasi, mengubah tanggal, memperpanjang, membatalkan, melakukan check-in dan check-out, menandai no-show, serta mengelola checklist blokir dan buka tanggal di OTA. Owner hanya dapat melihat (read-only). | Lia Kartika (FS) & Yuslani (FS) |
| Pencatatan & Verifikasi Pembayaran (DP/Pelunasan) | Wajib | Pengelola mencatat DP dan pelunasan setelah memverifikasi bukti pembayaran. Sistem memvalidasi nominal (DP minimum, tidak melebihi tagihan), menerbitkan invoice, dan hanya mengizinkan check-in setelah lunas. | Lia Kartika (FS) & Yuslani (FS) |
| Dashboard Owner (Pemantau) & Laporan Pembayaran | Wajib | Owner melihat pendapatan, okupansi, piutang, jumlah double booking yang dicegah, grafik, kalender, laporan pembayaran (dapat diekspor ke CSV), dan log aktivitas tanpa menunggu rekap mingguan. | Lia Kartika (FS) & Yuslani (FS) |
| Back-end, Penyimpanan Data, dan Deployment Online | Wajib | Aplikasi dapat dibuka publik lewat browser (hosting) termasuk di ponsel melalui tautan online. Data reservasi tersimpan permanen di database layanan online (bukan penyimpanan lokal yang dapat terhapus saat server restart) dan masukan divalidasi di back-end. Setiap aksi pengguna memberi tanda berhasil, gagal, atau sedang diproses. Kode dikelola di Git dan GitHub. | Lia Kartika (FS) & Yuslani (FS) |
| Integrasi WhatsApp untuk Konfirmasi Reservasi | Tambahan | Customer diarahkan ke WhatsApp Business Pengelola lewat tombol dengan pesan terisi otomatis, dan Pengelola dapat mengirim invoice PDF ke tamu lewat WhatsApp dengan satu klik. | Lia Kartika (FS) & Yuslani (FS) |
| Widget Chatbot di Website dan Tombol WhatsApp Berformat Data Pelanggan | Tambahan | Pelanggan dapat bertanya lewat jendela chat di website. Untuk reservasi, urusan yang ditangani admin (ubah atau batal pesanan, keluhan, pembayaran), dan pertanyaan yang tidak terjawab (tiga pemicu: kelas hubungi_admin, keyakinan model di bawah ambang, atau jawaban tidak ada di dokumen RAG), tombol membuka WhatsApp admin dengan pesan yang sudah berisi format data pelanggan (nama, asal, tanggal dan jam check-in, jam check-out, tipe kamar, jumlah tamu, kendaraan). | Lia Kartika (FS) & Yuslani (FS) |
| Akses Layanan AI Lewat Back-end, Riwayat Hasil AI, dan Penanganan Kegagalan | Tambahan | Browser hanya memanggil back-end, dan back-end memanggil layanan AI dengan key milik server. Hasil layanan AI (jenis, masukan tanpa nomor telepon, keluaran, status, lama respons) tersimpan di database. Bila layanan AI gagal, pengguna melihat pesan jelas dan form manual tetap berfungsi. | Lia Kartika (FS) & Yuslani (FS) |
| Integrasi Dasbor Data Science ke Aplikasi Web | Tambahan | Menu Analitik pada dashboard Owner menyematkan dasbor DS melalui back-end. | Lia Kartika (FS) & Yuslani (FS) |
| Log Aktivitas & Kontrol Akses Peran | Tambahan | Setiap pembuatan/perubahan reservasi, pembayaran, dan percobaan double booking yang ditolak tercatat (siapa, kapan, apa). Hak akses dibedakan antara Owner (pemantau dan data master) dan Pengelola (operasional). | Lia Kartika (FS) & Yuslani (FS) |
| Halaman Verifikasi Invoice Publik | Tambahan | Customer yang tidak memiliki akun dapat membuka tautan pada invoice untuk memeriksa keaslian dokumen dan mencocokkan nominal, total, serta status pembayaran reservasinya. | Lia Kartika (FS) & Yuslani (FS) |
| Pengujian Fungsional & Modul QA | Wajib | Seluruh skenario pengujian (reservasi valid, bentrok, pembatalan, cetak PDF, hak akses) lolos tanpa kendala fatal, termasuk tes otomatis pada aplikasi; hasil dicatat pada lembar rekap pengujian. | Susan Jong (AE) & Tim Capstone |

### Yang Tidak Dikerjakan (Out of Scope)

- Integrasi API langsung ke sistem internal OTA pihak ketiga (Agoda, Tiket.com, Traveloka)
- Gerbang pembayaran otomatis (payment gateway)
- Pembukuan akuntansi keuangan kompleks (laba/rugi)
- Modul absensi/penggajian karyawan
- Pemesanan mandiri oleh pelanggan (tidak ada akun pelanggan; pemesanan melalui WhatsApp Pengelola)
- Sewa per jam dan paket transit 6 jam dengan jeda pembersihan otomatis (Owner menyebut paket transit dan jeda 2 jam → masuk pengembangan lanjutan)
- Unggah foto bukti pembayaran pada sistem

### Hasil Akhir yang Dikumpulkan

- Aplikasi web MVP yang dapat dijalankan dan dibuka secara online
- Kode sumber (source code repository)
- Layanan AI (FastAPI beserta dokumentasi Swagger)
- Dasbor Data Science
- README (masalah model, sumber data, perbandingan model, hasil uji)
- Dokumen panduan penggunaan (user manual)
- Lembar rekapitulasi pengujian bersama UMKM

### Perubahan dari Use Case

- **Tipe kamar bertambah.** Use case mencatat tiga tipe kamar; informasi terbaru dari Owner menyebut empat tipe (ditambah Teras House) dengan total 6 unit (Standard Room AC 3, Standard Room Kipas 1, Mezanin 1, Teras House 1). Alasan: mengikuti kondisi usaha yang sebenarnya.
- **Website informasi publik dan chatbot AI ditambahkan.** Alasan: memenuhi checklist teknis wajib capstone (fitur generatif AI, layanan model AI, dan integrasi data) sekaligus mengurangi pertanyaan berulang di WhatsApp. Customer tetap tidak memiliki akun dan tidak memesan sendiri, sesuai use case; pemesanan tetap lewat WhatsApp Pengelola.
- **Smart WhatsApp Extractor ditambahkan.** Alasan: mengurangi input ulang dari WhatsApp ke form, salah satu penyebab pencatatan terlambat. Pengelola tetap memeriksa dan menyimpan sendiri.
- **Validasi bentrok berbasis rentang tanggal (check-in dan check-out).** Sewa per jam/transit dan jeda pembersihan otomatis tidak masuk MVP dan dicatat sebagai pengembangan lanjutan. Alasan: use case menetapkan validasi berdasarkan rentang check-in dan check-out serta waktu pengerjaan empat minggu.
- **Verifikasi pembayaran berupa pencatatan oleh Pengelola setelah bukti dicek lewat WhatsApp; unggah foto bukti belum termasuk MVP.** Alasan: fokus pada alur utama dan keterlacakan nominal lewat invoice.
- **Aturan operasional mengikuti jawaban Owner (5 November):** DP minimal 50%, batas bayar DP 1×24 jam, dan tamu yang tidak hadir sampai pukul 18.00 dianggap tidak hadir dengan DP hangus. Konfigurasi webapp yang sebelumnya memakai usulan sementara (6 jam dan 22:00) disesuaikan, dan pembacaan batas 18.00 masih dikonfirmasi.

---

## C. Jadwal Pengerjaan

| Waktu | Pekerjaan | Hasil yang Ditargetkan | Penanggung Jawab |
|-------|-----------|------------------------|------------------|
| **Minggu 1**<br>20–26 Nov 2026 | • Menyepakati kontrak API dan format data antar-tim (endpoint, request/response, kode error, kolom CSV, dataset intent).<br>• Menyesuaikan konfigurasi aturan operasional dengan jawaban Owner (DP 50%, batas bayar 1×24 jam, tidak hadir pukul 18.00) dan menindaklanjuti jawaban Owner yang masih bertentangan (harga per tipe, jeda pembersihan, pendingin, dapur).<br>• Menyiapkan repositori Git/GitHub dan deploy awal aplikasi (database online).<br>• Merumuskan pertanyaan bisnis, kamus data, dan memulai wrangling Laporan Data Tamu serta data dummy.<br>• Menyusun pedoman pelabelan dan draf dataset intent 5 kelas; menyiapkan kerangka layanan FastAPI. Memilih penyedia API generatif dan menulis entri dokumen RAG dari jawaban Owner. | Kontrak API dan data tertulis; repositori aktif; aplikasi dasar dapat dibuka online; pertanyaan bisnis dan kamus data v1; draf dataset intent. | Seluruh Tim (kontrak API, jadwal)<br>Full-Stack (Lia, Yuslani)<br>Data Science (Dewa, Dzakwan)<br>AI Engineer (Susan, Kaisya) |
| **Minggu 2**<br>27 Nov–3 Des 2026 | • Full-Stack: ekspor CSV reservasi tanpa data pribadi, tabel riwayat hasil AI, tombol WhatsApp berformat data pelanggan, status loading pada tombol, panggilan classifier dari chatbot, dan tombol pengisi form Reservasi Baru dari pesan WhatsApp.<br>• AI Engineer: preprocessing, melatih dan membandingkan minimal 2 model intent, layanan FastAPI online dengan Swagger, dan memulai RAG serta Smart WhatsApp Extractor.<br>• Data Science: wrangling selesai, EDA, pemeriksaan kualitas data, dataset intent final (latih/validasi/uji), dan memulai dasbor.<br>• Menguji fitur sejak selesai dikembangkan dan mencatat hasil pada lembar rekap pengujian. | Data bersih dan laporan kualitas data; model intent terlatih beserta tabel perbandingan; layanan FastAPI online; fitur Full-Stack tahap 2 berjalan. | Full-Stack (Lia, Yuslani)<br>AI Engineer (Susan, Kaisya)<br>Data Science (Dewa, Dzakwan)<br>Seluruh Tim (pengujian) |
| **Minggu 3**<br>4–10 Des 2026 | • Menghubungkan seluruh bagian: aplikasi web ↔ layanan intent/RAG ↔ dasbor DS (menu Analitik).<br>• Menyelesaikan RAG, Smart WhatsApp Extractor, dan cek ketersediaan lewat chatbot, serta uji 10 pertanyaan di luar konteks; menyelesaikan dasbor, filter, dan unggah CSV.<br>• Deploy seluruh layanan; uji alur utama end-to-end dari cek ketersediaan hingga invoice dan check-out.<br>• Target: seluruh fitur utama terhubung dan berjalan pada akhir minggu ketiga. | Alur utama berjalan end-to-end secara online; seluruh layanan terhubung; rekomendasi bisnis tersusun. | Seluruh Tim (integrasi, uji end-to-end)<br>AI Engineer (Susan, Kaisya)<br>Data Science (Dewa, Dzakwan)<br>Full-Stack (Lia, Yuslani) |
| **Minggu 4**<br>11–20 Des 2026 | • Pengujian menyeluruh sesuai rencana pengujian (valid, bentrok, input salah, layanan AI gagal, tampilan HP, hak akses).<br>• Demo kepada Owner, mengumpulkan umpan balik, mengisi lembar rekap pengujian bersama UMKM, dan memperbaiki masalah.<br>• Melengkapi README (masalah model, sumber data, perbandingan model, hasil uji), user manual, dan merapikan repositori.<br>• Mengumpulkan hasil akhir. | Aplikasi MVP stabil dan online; dokumentasi lengkap; rekap pengujian bersama UMKM; hasil akhir dikumpulkan. | Seluruh Tim (pengujian, demo, perbaikan)<br>Susan Jong (AE) & Tim Capstone (QA)<br>Kaisya (AE) (user manual)<br>Dewa Bagus P. C. P. (ketua tim) (pengumpulan) |

---

## D. Sumber Daya Proyek

| Sumber Daya / Fungsi | Ketersediaan / Izin | Batas Pemakaian | Pilihan Pengganti |
|----------------------|---------------------|-----------------|-------------------|
| Contoh format Laporan Data Tamu (lembar September 2026) | Sudah tersedia, tetapi hanya contoh format dari tempat lain, bukan data 33HomeStay (konfirmasi tim 10 November). | Hanya dipakai untuk melihat struktur kolom. Pola permintaan, harga, dan nomor kamar di dalamnya tidak dipakai. | Rekap data tamu 33HomeStay dari Owner bila diterima, dan data dummy berlabel sintetis. |
| Rekap data tamu 33HomeStay bulan lain (analisis dan dasbor) | Owner menyatakan bisa dibagikan tanpa nama dan nomor. Penerimaan datanya perlu dikonfirmasi. | Hanya data tanpa identitas pribadi pelanggan. | Data dummy 24 bulan sesuai format laporan fisik, ditandai sintetis. |
| Hotel Booking Demand Dataset (Kaggle) (Rujukan arsitektur) | Tersedia secara terbuka dengan lisensi CC BY 4.0 (Antonio, Almeida & Nunes, 2019, Data in Brief 22:41–49). | Hanya acuan struktur atribut dan pemodelan; wajib mencantumkan atribusi; bukan data 33HomeStay. | Data sintetis internal tim. |
| Data dummy reservasi dan dataset intent buatan tim | Dibuat sendiri oleh tim Data Science dan ditandai sintetis; pedoman pembuatan dan keterbatasannya ditulis di README. | Hanya untuk pengembangan dan pengujian; tidak mewakili kondisi nyata sehingga tidak dipakai untuk klaim akurasi operasional. | Data asli yang telah disensor Owner. |
| Jawaban dan dokumen informasi usaha dari Owner (basis pengetahuan RAG) | Sebagian sudah diterima. Jawaban yang saling bertentangan (harga, jeda pembersihan, pendingin, dapur) ditahan sampai dikonfirmasi. | Hanya informasi usaha tanpa data pribadi tamu. Entri yang tidak boleh dijawab chatbot dialihkan ke admin. | Entri contoh berlabel sampel selama data belum lengkap. |
| Laravel 12 (PHP 8.2+), Tailwind CSS, Alpine.js, Chart.js: aplikasi web, antarmuka, dan grafik | Sudah tersedia (open source); aplikasi dasar sudah dibangun. | Lisensi open source (MIT); aset CDN memerlukan koneksi internet. | Aset dipaketkan lokal memakai Vite. |
| Database: SQLite (pengembangan), PostgreSQL atau MySQL online (produksi): penyimpanan permanen | SQLite sudah tersedia; database online perlu disiapkan di akun tim. | Kuota dan batas penyimpanan paket gratis. | Layanan database gratis lain atau MySQL pada hosting lain. |
| DomPDF: pembuat invoice PDF | Sudah tersedia (open source). | Dukungan CSS dan font terbatas pada tampilan invoice sederhana. | Pustaka PDF lain berbasis browser. |
| API model bahasa proprietary: chatbot, RAG, dan Smart WhatsApp Extractor | Perlu API key akun tim (disimpan di server, tidak dicantumkan di dokumen); aplikasi tetap berjalan tanpa key. | Belum memutuskan berbayar atau tidak; berbayar per pemakaian atau terbatas kuota; chatbot dibatasi 20 pesan per menit per IP dan 600 karakter per pesan; kuota API berdasarkan model yang dipilih. | Mode dasar berbasis aturan (sudah ada), API proprietary lain, atau SLM open-source yang di-host lokal. |
| Python (FastAPI, scikit-learn, pandas) dan Jupyter/Colab: layanan model dan analisis data | Tersedia (open source). | Model klasik berjalan pada CPU; paket gratis memori terbatas dan GPU pada spesifikasi laptop. | Flask sebagai pengganti FastAPI. |
| Model embedding dan vector database (ChromaDB atau FAISS): RAG chatbot | Open source; pilihan model embedding ditentukan tim AI. | Ukuran model dan memori pada paket gratis. | Pencarian dokumen berbasis kata kunci (TF-IDF/BM25). |
| Streamlit: dasbor pemantauan bisnis | Tersedia gratis; hosting dapat memakai Streamlit Community Cloud. | Aplikasi gratis dapat tidur (tidak aktif sementara) bila lama tidak diakses dan memiliki batas sumber daya. | Dasbor langsung di aplikasi web memakai Chart.js. |
| Hosting dan deployment online (misalnya Render, Neon, Streamlit Community Cloud) | Perlu disiapkan akun tim dibuat pada minggu 1. | Batas paket gratis (layanan non-active sementara, kuota, biaya hosting jika ada, penyimpanan sementara); ketentuan terbaru diperiksa sebelum memilih. | Layanan gratis lain atau demo lokal dengan tunnel. |
| GitHub: pengelolaan riwayat kode | Perlu dibuat repositori tim pada minggu 1. | Batas fitur paket gratis. | GitLab. |
| WhatsApp Business milik Pengelola: kanal pemesanan (tautan wa.me berisi pesan terisi) | Sudah tersedia; nomor ditentukan Owner. | Hanya tautan pesan terisi otomatis; tidak memakai API WhatsApp sehingga tidak ada biaya. | WhatsApp Cloud API dengan nomor uji (opsional). |

### Alur Kerja Aplikasi

**Website publik dan dashboard staf** dilayani oleh aplikasi back-end Laravel yang menjalankan validasi, aturan bisnis, dan penyimpanan ke database. Browser **tidak pernah** memanggil layanan AI atau Data Science secara langsung — semua panggilan melewati back-end dengan kunci milik server.

**Alur reservasi:**  
Pengelola memasukkan data reservasi pada aplikasi → back-end memeriksa bentrok tanggal di dalam transaksi database → bila aman, data disimpan dan kalender diperbarui untuk semua pengguna → Pengelola mencatat pembayaran → back-end membuat invoice PDF dari data pembayaran dan menyimpannya sebagai riwayat.

**Alur chatbot:**  
Pengunjung mengetik pertanyaan di website → back-end meneruskan teks ke layanan FastAPI untuk klasifikasi intent (di sinilah model AI tim dipakai) → pesan dirutekan:  
- `reservasi` dan `hubungi_admin` → menampilkan tombol WhatsApp Pengelola berisi format data pelanggan  
- `cek_ketersediaan` → dijawab dari data reservasi di database (angka bukan dari model generatif)  
- `tanya_info` → dijawab sistem RAG dengan model generatif  
- `lainnya` → ditolak dengan sopan  

Jawaban ditampilkan di website dan ringkasan hasilnya (tanpa nomor telepon) disimpan di database. Bila layanan AI gagal, back-end memakai mode dasar berbasis aturan dan menampilkan pesan yang jelas.

**Alur form dari pesan WhatsApp:**  
Pengelola menempelkan pesan WhatsApp pelanggan pada Reservasi Baru → back-end memanggil FastAPI (`/api/v1/booking/extract`) → hasil JSON mengisi form, kolom yang tidak disebut dikosongkan dan ditandai → Pengelola memeriksa, melengkapi, lalu menyimpan, dan validasi bentrok tetap dijalankan back-end. Bila layanan gagal, form manual tetap berfungsi.

**Alur analitik:**  
Data reservasi dan pembayaran diekspor sebagai CSV tanpa data pribadi → tim Data Science melakukan wrangling dan analisis, dan menayangkan dasbor Streamlit → aplikasi web menyematkan dasbor tersebut pada dashboard Owner melalui back-end.

---

## E. Risiko dan Cara Mengatasinya

| Kendala dan Dampak | Cara Menangani | Rencana Cadangan | Penanggung Jawab |
|--------------------|----------------|------------------|------------------|
| Anggota tim mengalami kendala kesehatan/kesibukan akademis. | Menerapkan peer programming per learning path (2 FS, 2 DS, 2 AE) agar tugas dapat saling dilapis. | Pembagian ulang beban kerja (task re-allocation) dalam rapat rutin tim. | Tim Capstone (Seluruh Anggota) |
| Durasi sewa bervariasi (harian vs transit 5–6 jam) berisiko bentrok, sedangkan MVP memvalidasi bentrok berdasarkan rentang tanggal harian. | Menetapkan batas MVP pada reservasi menginap per tanggal (check-in dan check-out), diuji dengan tes otomatis termasuk kasus batas hari yang sama. | Sewa per jam/transit dicatat sebagai pengembangan lanjutan; sementara itu status kamar Dibersihkan dipakai sebagai jeda setelah check-out. | Lia Kartika (FS), Yuslani (FS), Dewa Bagus Pradnyaan Cahyadi Putra (DS) & Dzakwan Muzakki (DS) |
| Tampilan aplikasi kurang nyaman di layar HP staf admin. | Menggunakan pendekatan mobile-first design dengan CSS responsif dan menguji di lebar layar ponsel. | Menyediakan mode tampilan ringkas (simplified mobile view). | Yuslani (FS) & Lia Kartika (FS) |
| Pengelola lupa memblokir atau membuka tanggal di OTA karena belum ada integrasi API sehingga double booking dari OTA masih mungkin. | Checklist OTA di dashboard (perlu diblokir dan perlu dibuka) serta pencatatan manual pesanan OTA dengan alur yang sama. | Pengingat berkala oleh Owner; integrasi sinkronisasi OTA dicatat sebagai pengembangan lanjutan. | Lia Kartika (FS) & Yuslani (FS) |
| Konfigurasi webapp (batas bayar DP 6 jam, batas check-in 22:00) berbeda dari jawaban Owner (1×24 jam, pukul 18:00), sehingga perilaku sistem dapat berbeda dari kebiasaan UMKM. | Menyesuaikan nilai konfigurasi dengan jawaban Owner pada minggu 1. Seluruh nilai tetap disimpan sebagai konfigurasi sehingga dapat diubah tanpa mengubah kode. | Mengonfirmasi pembacaan batas 18.00 ke Owner dan menjelaskan perbedaan pada demo bila belum selesai. | Dewa Bagus Pradnyaan Cahyadi Putra (DS) (ketua tim) & Lia Kartika (FS) |
| API chatbot proprietary lambat, tidak dapat diakses, atau kuota habis sehingga chatbot tidak menjawab. | Batas waktu panggilan, mode dasar berbasis aturan otomatis, pesan jelas bagi pengguna, serta tombol WhatsApp dan form manual tetap berfungsi. | Menjalankan SLM open-source lokal atau API proprietary lain yang dapat diakses back-end. | Susan Jong (AE), Kaisya Anindya Callista Putri Kusyanto (AE) & Lia Kartika (FS) |
| Layanan hosting gratis non-active atau berbatas sehingga demo lambat atau data tidak tersimpan. | Memakai database online untuk penyimpanan permanen, memanaskan layanan sebelum demo, dan memantau kuota. | Demo lokal dengan tunnel dan data cadangan (seed) yang siap dimuat. | Lia Kartika (FS) & Yuslani (FS) |
| Perubahan kontrak API antar-tim di tengah pengembangan sehingga integrasi gagal. | Menyepakati kontrak pada minggu 1, memberi versi `/api/v1`, dan menguji integrasi di akhir setiap minggu. | Menggunakan stub/mock respons agar pengembangan tidak berhenti. | Dewa Bagus Pradnyaan Cahyadi Putra (DS) (ketua tim) |
| Kebocoran data pribadi tamu (nama, nomor telepon) ke layanan AI atau laporan. | Sistem menerapkan protokol keamanan data dengan menyandikan (encoding) seluruh informasi sensitif, membatasi pengiriman data pribadi ke layanan AI, serta menjamin proses ekspor CSV dilakukan secara anonim tanpa menyertakan nama dan nomor telepon. | Menyamarkan atau menghapus kolom sensitif sebelum data dibagikan. | Tim Capstone (Seluruh Anggota) |
| Jawaban Owner saling bertentangan (harga per tipe, jeda pembersihan 2 atau 1 jam, pendingin, dapur, tempat tidur Mezanin), sehingga RAG memberi informasi keliru. | Entri terkait ditahan dan pertanyaannya dialihkan ke admin, lalu daftar konflik dikirim ulang ke Owner. | Entri contoh berlabel sampel sampai dikonfirmasi. | Susan Jong (AE) & Kaisya Anindya Callista Putri Kusyanto (AE) |
| Data intent sintetis tidak mirip chat asli, sehingga skor model tinggi tetapi menyesatkan, dan pesan urusan admin bisa dijawab bot sendiri. | Data uji ditulis penulis berbeda, memuat kasus sulit, dibandingkan dengan aturan kata kunci, dan recall eskalasi dilaporkan. Pesan yang ragu dialihkan ke admin. | Contoh chat asli tanpa nama dan nomor (Owner mengizinkan) dipakai sebagai uji tambahan. | Kaisya Anindya Callista Putri Kusyanto (AE) & Dzakwan Muzakki (DS) |
| Pemilihan dan perbandingan model klasifikasi intent yang akan dikembangkan sendiri serta mekanisme integrasinya dengan backend Laravel belum ditetapkan secara final. Hal ini berpotensi menyebabkan ketidaksesuaian performa model dalam mengenali maksud pesan pelanggan, keterlambatan integrasi layanan AI, serta inkonsistensi format komunikasi antara model dan aplikasi. | Melakukan preprocessing dan pengujian dataset, melatih serta mengevaluasi model klasifikasi intent, kemudian memilih model dengan performa terbaik. Memastikan integrasi model melalui API berjalan sesuai kebutuhan sistem. | Melakukan penyesuaian dataset dan pelatihan ulang jika performa model belum optimal. Jika layanan AI mengalami kendala atau kurang informasi, menggunakan mekanisme rule-based atau mengarahkan pelanggan untuk menghubungi admin melalui WhatsApp. | Susan Jong (AE), Kaisya Anindya Callista Putri Kusyanto (AE) & Lia Kartika (FS) |

---

## F. Rencana Pengujian

| Fitur yang Diuji | Cara Menguji | Hasil yang Diharapkan | Penanggung Jawab |
|------------------|--------------|-----------------------|------------------|
| Validasi Bentrok (Pencegahan Double Booking) | Input reservasi unit AC-1 (check-in 10 Nov, check-out 12 Nov). Lalu input reservasi kedua pada AC-1 (check-in 11 Nov, check-out 13 Nov). Ulangi dengan check-in 12 Nov dan check-out 13 Nov. | Reservasi pertama tersimpan. Kedua ditolak dengan peringatan bentrok jadwal beserta saran alternatif. Ketiga (check-in sama dengan check-out sebelumnya) diterima. | Lia Kartika (FS), Yuslani (FS) & Dewa Bagus Pradnyaan Cahyadi Putra (DS) |
| Penyimpanan Reservasi Valid | Input reservasi unit AC-2 pada tanggal kosong, catat DP minimum, lalu catat pelunasan. | Reservasi tersimpan berstatus Menunggu DP, menjadi Terkonfirmasi setelah DP, dan Lunas setelah pelunasan; kalender menandai unit Dibooking. | Lia Kartika (FS) |
| Validasi Pembayaran | Catat DP di bawah minimum, lalu catat nominal yang melebihi sisa tagihan. | Keduanya ditolak dengan pesan yang menyebut batas nominal. | Yuslani (FS) |
| Cetak Invoice Digital (PDF) | Klik PDF pada pembayaran, lalu buka tautan Verifikasi pada invoice. | Berkas PDF terunduh dengan rincian nama, kamar, nominal, DP, dan sisa tagihan yang sesuai; halaman verifikasi menampilkan nominal yang sama. | Lia Kartika (FS) & Yuslani (FS) |
| Validasi Form Input Kosong | Kosongkan kolom nama tamu atau tanggal check-in, lalu klik simpan. | Sistem menolak menyimpan dan menampilkan peringatan wajib isi. | Lia Kartika (FS) |
| Check-in dan Pelunasan | Coba check-in saat belum lunas lalu catat pelunasan dan check-in kembali. | Check-in awal ditolak dengan pesan sisa tagihan; setelah lunas check-in berhasil dan kondisi kamar menjadi Terisi. | Yuslani (FS) |
| Ubah Tanggal dan Perpanjang Inap | Perpanjang reservasi hingga menabrak reservasi berikutnya lalu perpanjang pada tanggal yang kosong. | Yang menabrak ditolak; yang kosong diterima dan total serta sisa tagihan dihitung ulang. | Lia Kartika (FS), Yuslani (FS) & Dewa Bagus Pradnyaan Cahyadi Putra (DS) |
| Pembatalan Otomatis dan No-Show | Biarkan reservasi Menunggu DP melewati batas waktu (scheduler berjalan); tandai no-show setelah batas jam check-in. | Reservasi batal otomatis dan tanggal tersedia kembali; pada no-show, DP ditandai hangus sesuai aturan dan unit tersedia kembali. | Lia Kartika (FS) & Yuslani (FS) |
| Hak Akses Peran | Owner membuka halaman Reservasi Baru; Pengelola membuka dashboard Owner; pengunjung tanpa login membuka halaman staf. | Akses ditolak (403) atau diarahkan ke halaman login. | Yuslani (FS) |
| Cek Ketersediaan Publik | Isi tanggal valid; isi check-out sebelum check-in; periksa tombol pada hasil. | Hasil sesuai ketersediaan; input salah ditolak dengan pesan; tidak ada pemesanan mandiri; tombol membuka WhatsApp dengan pesan terisi. | Lia Kartika (FS) |
| Tampilan di Ponsel | Buka website dan dashboard pada ponsel (lebar sekitar 375 px). | Seluruh fungsi dapat dipakai nyaman; tabel lebar menggulir di dalam wadahnya. | Lia Kartika (FS) & Yuslani (FS) |
| Model Klasifikasi Intent | Evaluasi pada data uji yang disisihkan untuk semua model yang dibandingkan. | Macro-F1 beserta interval kepercayaan, akurasi, laporan per kelas, recall eskalasi, dan persentase pesan dialihkan tercatat di README; model terpilih lebih baik dari pembanding pada metrik yang sama. | Susan Jong (AE) & Kaisya Anindya Callista Putri Kusyanto (AE) |
| Layanan FastAPI Intent | Kirim teks valid, teks kosong, teks lebih dari 600 karakter, dan kunci API salah lalu matikan layanan. | Respons 200 berisi intent dan confidence; 422 untuk input tidak valid; 401 untuk kunci salah; saat layanan mati aplikasi tetap berfungsi dengan pesan jelas. | Kaisya Anindya Callista Putri Kusyanto (AE) & Lia Kartika (FS) |
| Chatbot di Luar Konteks | Ajukan minimal 10 pertanyaan di luar konteks usaha dan 10 pertanyaan dalam konteks. | Pertanyaan di luar konteks ditolak dengan sopan beserta arahan topik; pertanyaan dalam konteks dijawab sesuai data; hasil dicatat di README. | Susan Jong (AE) & Kaisya Anindya Callista Putri Kusyanto (AE) |
| Chatbot saat Layanan AI Gagal | Nonaktifkan API key atau putuskan koneksi layanan AI. | Chatbot beralih ke mode dasar dengan pesan jelas; tombol WhatsApp tetap berfungsi. | Lia Kartika (FS) & Susan Jong (AE) |
| Smart WhatsApp Extractor | Masukkan 20 sampai 30 obrolan fiktif dengan jawaban benar tulisan tangan (berformat dari tombol dan teks bebas), lalu ulangi dengan teks kosong dan pesan tanpa tanggal. Tempel satu pesan pada Reservasi Baru. | Persentase cocok persis per kolom dan tingkat halusinasi tercatat. Bagian yang tidak disebut kosong dan ditandai. Form terisi untuk diperiksa Pengelola, dan teks kosong ditolak dengan pesan jelas. | Susan Jong (AE) & Lia Kartika (FS) |
| Cek Ketersediaan lewat Chatbot | Tanyakan ketersediaan pada tanggal yang seluruh unit tipenya sudah ber-DP, pada tanggal kosong, dan dengan reservasi yang belum ber-DP. Coba tanggal lampau dan tanggal yang belum lengkap. | Jawaban sesuai data reservasi di database. Reservasi belum ber-DP tidak mengurangi ketersediaan. Tanggal tidak valid ditolak dan tanggal yang kurang ditanyakan kembali. | Kaisya Anindya Callista Putri Kusyanto (AE), Lia Kartika (FS) & Yuslani (FS) |
| Dasbor Streamlit | Ubah filter periode, tipe kamar, dan kanal; unggah CSV baru dan CSV yang tidak valid. | Indikator dan grafik berubah sesuai filter; CSV valid memperbarui data; CSV tidak valid ditolak dengan pesan. | Dewa Bagus Pradnyaan Cahyadi Putra (DS) |
| Kualitas Data | Jalankan pemeriksaan kualitas pada data sebelum dipakai sistem. | Laporan menunjukkan tidak ada duplikat atau tumpang tindih tanggal dan total konsisten. | Dzakwan Muzakki (DS) & Dewa Bagus Pradnyaan Cahyadi Putra (DS) |
| Deployment Online | Buka tautan publik dari ponsel dan jaringan berbeda; tambah reservasi lalu muat ulang halaman. | Halaman dapat dibuka; data tetap tersimpan setelah muat ulang dan restart layanan. | Lia Kartika (FS) & Yuslani (FS) |
| Tes Otomatis Aplikasi | Jalankan tes otomatis aplikasi (bentrok tanggal, alur pembayaran, hak akses). | Seluruh tes lulus dan hasilnya dicatat sebelum demo. | Yuslani (FS) & Lia Kartika (FS) |

**Pencatatan hasil uji:**  
Spreadsheet bersama “Rekap Pengujian A26-CS026” (kolom: ID uji, fitur, skenario, hasil, status, penanggung jawab, tanggal, dan tautan bukti/tangkapan layar) serta GitHub Issues untuk setiap masalah yang ditemukan. Lembar rekap pengujian bersama UMKM dilampirkan pada pengumpulan akhir.

Perbaiki masalah yang ditemukan, lalu uji kembali fitur tersebut.

---

*Dokumen ini dikonversi dari PDF Project Plan Asah 2026 Capstone Project A26-CS026 menjadi format Markdown agar lebih mudah dibaca dan diproses oleh AI.*
