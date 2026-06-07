# 📖 BUKU PANDUAN PENGGUNA
# Pixora Mobile - Aplikasi Booking Studio Fotografi

---

## Daftar Isi

1. [Pendahuluan](#1-pendahuluan)
2. [Persyaratan Sistem](#2-persyaratan-sistem)
3. [Instalasi Aplikasi](#3-instalasi-aplikasi)
4. [Panduan Penggunaan](#4-panduan-penggunaan)
   - 4.1 [Registrasi Akun Baru](#41-registrasi-akun-baru)
   - 4.2 [Login ke Akun](#42-login-ke-akun)
   - 4.3 [Halaman Beranda](#43-halaman-beranda)
   - 4.4 [Melihat Paket Fotografi](#44-melihat-paket-fotografi)
   - 4.5 [Detail Paket](#45-detail-paket)
   - 4.6 [Melakukan Booking](#46-melakukan-booking)
   - 4.7 [Pembayaran](#47-pembayaran)
   - 4.8 [Status Booking & Invoice](#48-status-booking--invoice)
   - 4.9 [AI Chatbot Assistant](#49-ai-chatbot-assistant)
   - 4.10 [Mengelola Profil](#410-mengelola-profil)
5. [Navigasi Aplikasi](#5-navigasi-aplikasi)
6. [FAQ (Pertanyaan yang Sering Ditanyakan)](#6-faq)
7. [Troubleshooting](#7-troubleshooting)
8. [Kontak & Bantuan](#8-kontak--bantuan)

---

## 1. Pendahuluan

**Pixora Mobile** adalah aplikasi mobile versi Flutter dari Pixora Studio — platform booking studio fotografi profesional. Aplikasi ini memungkinkan pengguna untuk:

- 📸 Melihat dan memilih paket fotografi
- 📅 Melakukan booking sesi foto secara online
- 💳 Membayar via berbagai metode pembayaran (Transfer Bank, QRIS, GoPay, dll)
- 🤖 Bertanya kepada AI Assistant tentang layanan studio
- 👤 Mengelola profil dan melihat riwayat booking

Aplikasi ini terhubung langsung ke backend Pixora (Laravel) sehingga data paket, jadwal, dan booking selalu sinkron dengan versi web.

---

## 2. Persyaratan Sistem

### Minimum Requirements

| Platform | Versi Minimum |
|----------|---------------|
| Android  | Android 5.0 (Lollipop) / API Level 21 |
| iOS      | iOS 12.0 |
| RAM      | Minimal 2 GB |
| Storage  | Minimal 100 MB ruang kosong |
| Internet | Koneksi internet aktif (Wi-Fi atau Data Seluler) |

### Rekomendasi

| Platform | Versi |
|----------|-------|
| Android  | Android 10+ |
| iOS      | iOS 15+ |
| RAM      | 4 GB atau lebih |

---

## 3. Instalasi Aplikasi

### Untuk Developer (Build dari Source)

1. **Pastikan Flutter SDK terinstall**
   ```bash
   flutter --version
   ```

2. **Clone/buka project**
   ```bash
   cd pixora_mobile
   ```

3. **Install dependencies**
   ```bash
   flutter pub get
   ```

4. **Konfigurasi API URL**
   
   Edit file `lib/config/api_config.dart`:
   ```dart
   static const String baseUrl = 'http://<IP_SERVER>:8000';
   ```
   
   - **Android Emulator**: `http://10.0.2.2:8000`
   - **iOS Simulator**: `http://localhost:8000`
   - **Device Fisik**: `http://<IP_KOMPUTER_ANDA>:8000`

5. **Jalankan aplikasi**
   ```bash
   flutter run
   ```

6. **Build APK (untuk distribusi)**
   ```bash
   flutter build apk --release
   ```
   File APK akan tersedia di: `build/app/outputs/flutter-apk/app-release.apk`

### Untuk Pengguna (Install APK)

1. Download file APK dari link yang disediakan
2. Buka file APK di perangkat Android
3. Jika diminta izin, aktifkan "Install dari sumber tidak dikenal" di Pengaturan
4. Ikuti proses instalasi
5. Buka aplikasi Pixora dari menu aplikasi

---

## 4. Panduan Penggunaan

### 4.1 Registrasi Akun Baru

1. Buka aplikasi Pixora
2. Pada halaman utama, tap tab **Profil** di navigation bar
3. Tap tombol **Daftar**
4. Isi formulir pendaftaran:
   - **Nama Lengkap** — Nama Anda (wajib)
   - **Email** — Alamat email aktif (wajib)
   - **No. Telepon** — Nomor HP yang bisa dihubungi (opsional)
   - **Password** — Minimal 6 karakter (wajib)
   - **Konfirmasi Password** — Ulangi password (wajib)
5. Tap tombol **Daftar**
6. Jika berhasil, Anda akan langsung masuk ke halaman utama

> **Tips:** Gunakan email yang aktif agar mudah menerima informasi booking.

### 4.2 Login ke Akun

1. Tap tab **Profil** di navigation bar
2. Tap tombol **Masuk**
3. Masukkan **Email** dan **Password**
4. Tap tombol **Masuk**
5. Atau pilih **Masuk dengan Google** untuk login via akun Google

> **Catatan:** Anda tetap bisa menjelajahi aplikasi tanpa login. Login diperlukan untuk mengelola profil dan melihat riwayat booking.

### 4.3 Halaman Beranda

Halaman Beranda adalah tampilan utama yang menampilkan:

- **Hero Banner** — Gambar utama dengan tagline studio
- **Statistik** — Jumlah klien puas, sesi foto, portofolio, dan rating
- **Paket Populer** — Scroll horizontal untuk melihat paket-paket unggulan
- **Galeri** — Grid foto hasil karya studio
- **Testimonial** — Ulasan dari klien sebelumnya
- **CTA (Call to Action)** — Tombol untuk langsung booking

**Cara Navigasi:**
- Swipe ke atas untuk scroll ke bawah
- Tap pada card paket untuk melihat detail
- Tap "Lihat Semua" untuk melihat semua paket
- Tap "Booking" untuk langsung ke halaman kalender

### 4.4 Melihat Paket Fotografi

1. Tap tab **Paket** (ikon kamera) di navigation bar
2. Anda akan melihat daftar semua paket fotografi yang tersedia
3. Setiap card paket menampilkan:
   - Gambar paket
   - Nama paket
   - Deskripsi singkat
   - Durasi sesi (jam)
   - Jumlah foto edit
   - Lokasi (Studio/Outdoor/Both)
   - Harga
   - Harga Down Payment (DP)
4. Tap pada card untuk melihat detail lengkap

### 4.5 Detail Paket

Halaman detail paket menampilkan informasi lengkap:

- **Gambar besar** paket (bisa di-swipe jika ada beberapa)
- **Nama & Rating** paket
- **Harga** mulai dari
- **Deskripsi** lengkap
- **Spesifikasi:**
  - ⏱️ Durasi sesi
  - 📷 Jumlah foto yang diedit
  - 📍 Jenis lokasi
- **Yang Termasuk dalam Paket** — Daftar item yang didapat
- **FAQ** — Pertanyaan umum tentang paket
- **Tombol "Booking Sekarang"** — Langsung ke halaman kalender

### 4.6 Melakukan Booking

Proses booking terdiri dari 3 langkah:

#### Langkah 1: Pilih Jadwal (Kalender)

1. Tap tab **Kalender** di navigation bar, atau tap "Booking" dari halaman lain
2. Anda akan melihat kalender bulanan
3. **Penanda warna pada tanggal:**
   - 🟢 **Hijau** = Semua slot tersedia
   - 🟡 **Kuning** = Sebagian slot tersedia
   - 🔴 **Merah** = Semua slot penuh
   - ⚫ **Abu-abu** = Tanggal sudah lewat
4. Tap pada tanggal yang diinginkan
5. Di bawah kalender, pilih slot waktu:
   - ☀️ **Pagi** (08:00 - 11:00)
   - 🌤️ **Siang** (13:00 - 16:00)
   - 🌙 **Sore** (17:00 - 20:00)
6. Slot yang berwarna abu-abu tidak bisa dipilih (sudah dibooking atau sudah lewat)
7. Tap **"Lanjut ke Booking"**

#### Langkah 2: Isi Form Booking

1. Pilih **Paket** yang diinginkan dari daftar
2. Isi data pemesan:
   - **Nama Lengkap** (wajib)
   - **No. Telepon** (wajib)
   - **Email** (opsional, untuk menerima invoice)
   - **Catatan Khusus** (opsional, misalnya: "konsep vintage outdoor")
3. Pilih **Tipe Pembayaran:**
   - 💳 **Full Payment** — Bayar penuh langsung
   - 💰 **Down Payment** — Bayar DP terlebih dahulu
4. Tap **"Proses Booking"**

#### Langkah 3: Pembayaran

1. Anda akan diarahkan ke halaman pembayaran Midtrans
2. Pilih metode pembayaran yang tersedia:
   - Transfer Bank (BCA, BNI, BRI, Mandiri)
   - QRIS
   - GoPay
   - ShopeePay
   - Alfamart / Indomaret
3. Ikuti instruksi pembayaran sesuai metode yang dipilih
4. Setelah pembayaran berhasil, status akan otomatis terupdate

> ⚠️ **Penting:** Booking memiliki batas waktu pembayaran **30 menit**. Jika tidak dibayar dalam waktu tersebut, booking akan otomatis kadaluarsa.

### 4.7 Pembayaran

**Metode Pembayaran yang Tersedia:**

| Metode | Cara Bayar |
|--------|------------|
| Transfer Bank | Transfer ke Virtual Account yang diberikan |
| QRIS | Scan QR Code menggunakan aplikasi e-wallet |
| GoPay | Bayar melalui aplikasi GoPay/Gojek |
| ShopeePay | Bayar melalui aplikasi Shopee |
| Alfamart/Indomaret | Tunjukkan kode pembayaran di kasir |

### 4.8 Status Booking & Invoice

Setelah pembayaran berhasil:

1. Anda akan melihat halaman **Detail Booking** yang menampilkan:
   - ✅ Status pembayaran (Lunas/DP Terbayar)
   - 📋 Kode booking
   - 📦 Detail paket, tanggal, dan waktu
   - 👤 Data pemesan
   - 💰 Informasi pembayaran
2. Tap **"Lihat Invoice"** untuk membuka invoice di browser
3. Invoice bisa didownload sebagai PDF

**Status Pembayaran:**
- 🟡 **Menunggu Pembayaran** — Belum dibayar
- 🟢 **Lunas** — Pembayaran penuh berhasil
- 🔵 **DP Terbayar** — Down payment berhasil
- 🔴 **Kadaluarsa** — Melewati batas waktu pembayaran
- ⚫ **Dibatalkan** — Booking dibatalkan

### 4.9 AI Chatbot Assistant

1. Tap tombol **AI** (ikon robot di tengah) di navigation bar
2. Anda akan masuk ke halaman chat
3. Ketik pertanyaan Anda, misalnya:
   - *"Ada paket apa saja?"*
   - *"Berapa harga paket wedding?"*
   - *"Jadwal kosong besok kapan?"*
   - *"Bagaimana cara booking?"*
   - *"Bisa reschedule?"*
4. AI akan menjawab berdasarkan data terbaru dari studio
5. AI mengetahui:
   - Informasi paket dan harga
   - Ketersediaan jadwal real-time
   - Kebijakan studio (reschedule, refund, dll)
   - Cara booking dan pembayaran

> **Tips:** Tanyakan dalam bahasa yang natural, AI akan memahami konteks pertanyaan Anda.

### 4.10 Mengelola Profil

1. Tap tab **Profil** di navigation bar
2. Jika sudah login, Anda akan melihat:
   - Avatar dengan inisial nama
   - Nama, email, dan nomor telepon
3. **Menu yang tersedia:**
   - ✏️ **Edit Profil** — Ubah nama dan telepon
   - 🔒 **Ubah Password** — Ganti password akun
   - 📜 **Riwayat Booking** — Lihat daftar booking
   - ℹ️ **Tentang Pixora** — Info versi aplikasi
   - ❓ **Bantuan** — FAQ dan kontak
   - 🚪 **Keluar** — Logout dari akun

**Mengubah Password:**
1. Tap "Ubah Password"
2. Masukkan password saat ini
3. Masukkan password baru (min. 6 karakter)
4. Konfirmasi password baru
5. Tap "Simpan"

---

## 5. Navigasi Aplikasi

Aplikasi Pixora menggunakan **Bottom Navigation Bar** dengan 5 menu:

| Ikon | Label | Fungsi |
|------|-------|--------|
| 🏠 | **Beranda** | Halaman utama dengan info studio |
| 📸 | **Paket** | Daftar paket fotografi |
| 🤖 | **AI** (tengah) | Chatbot AI assistant |
| 📅 | **Kalender** | Jadwal booking dan pilih tanggal |
| 👤 | **Profil** | Pengaturan akun dan riwayat |

Tombol AI di tengah berukuran lebih besar dan berwarna gradient rose-pink sebagai fitur utama.

---

## 6. FAQ

### Umum

**Q: Apakah harus login untuk booking?**
A: Tidak, Anda bisa melakukan booking tanpa login. Namun login diperlukan untuk melihat riwayat booking di profil.

**Q: Apakah data booking aman?**
A: Ya, aplikasi menggunakan enkripsi dan token untuk mengamankan data Anda.

**Q: Bisa booking lewat website juga?**
A: Ya! Pixora tersedia di versi web dan mobile. Data sinkron antara keduanya.

### Booking & Pembayaran

**Q: Berapa batas waktu pembayaran?**
A: 30 menit sejak booking dibuat. Setelah itu booking otomatis kadaluarsa.

**Q: Apakah bisa reschedule?**
A: Ya, reschedule bisa dilakukan maksimal H-3 sebelum sesi. Hubungi admin.

**Q: Apakah DP bisa refund?**
A: DP tidak dapat dikembalikan jika pembatalan kurang dari H-7 sebelum sesi.

**Q: Berapa lama proses edit foto?**
A: 14-21 hari kerja setelah sesi foto selesai.

### Teknis

**Q: Aplikasi tidak bisa terhubung ke server?**
A: Pastikan koneksi internet aktif dan server backend berjalan.

**Q: Halaman pembayaran tidak muncul?**
A: Coba refresh halaman atau restart aplikasi.

---

## 7. Troubleshooting

### Masalah Umum dan Solusi

| Masalah | Solusi |
|---------|--------|
| Aplikasi loading terus | Periksa koneksi internet, restart aplikasi |
| Login gagal | Periksa email/password, reset password jika lupa |
| Paket tidak muncul | Tarik ke bawah untuk refresh, periksa koneksi |
| Kalender tidak update | Pindah bulan lalu kembali untuk refresh data |
| Pembayaran error | Pastikan saldo cukup, coba metode lain |
| AI tidak merespon | Periksa koneksi internet, coba lagi |
| Gambar tidak tampil | Periksa koneksi internet, cache akan terisi |

### Tips Umum

1. **Selalu gunakan versi terbaru** aplikasi
2. **Pastikan koneksi internet stabil** saat melakukan pembayaran
3. **Screenshot kode booking** Anda sebagai cadangan
4. **Jangan tutup aplikasi** saat proses pembayaran berlangsung

---

## 8. Kontak & Bantuan

Jika Anda memerlukan bantuan lebih lanjut:

- **📍 Alamat Studio:** Kruwet, Teluk, Kec. Purwokerto Sel., Kabupaten Banyumas, Jawa Tengah 53145
- **📞 Telepon:** +62 812-3456-7890
- **📧 Email:** hello@pixora.com
- **📸 Instagram:** @pixora.studio
- **🎵 TikTok:** @pixora
- **⏰ Jam Operasional:** 08:00 - 20:00 WIB

---

## Informasi Teknis (Untuk Developer)

### Arsitektur Aplikasi

```
pixora_mobile/
├── lib/
│   ├── main.dart              # Entry point
│   ├── config/                # Konfigurasi (API, theme, routes)
│   ├── models/                # Data models (User, Package, Booking)
│   ├── services/              # API & storage services
│   ├── providers/             # State management (Provider)
│   ├── screens/               # Halaman-halaman UI
│   ├── widgets/               # Widget reusable
│   └── utils/                 # Utility (formatters, validators)
├── assets/                    # Asset gambar dan font
└── pubspec.yaml               # Dependencies
```

### Teknologi yang Digunakan

| Teknologi | Fungsi |
|-----------|--------|
| Flutter 3.x | Framework UI cross-platform |
| Dart | Bahasa pemrograman |
| Provider | State management |
| HTTP | HTTP client untuk API |
| SharedPreferences | Penyimpanan lokal |
| WebView | Halaman pembayaran Midtrans |
| TableCalendar | Widget kalender |
| CachedNetworkImage | Cache gambar dari internet |
| Google Fonts | Font Poppins dan Inter |
| Flutter Animate | Animasi UI |

### API Backend

Aplikasi terhubung ke backend **Laravel** dengan endpoints:
- `POST /api/login` — Login
- `POST /api/register` — Register
- `GET /api/packages` — Daftar paket
- `GET /api/calendar/data` — Data kalender
- `POST /api/booking/store` — Buat booking
- `POST /ai/chat` — AI chatbot

---

> **© 2024 Pixora Studio. All rights reserved.**
> 
> Buku panduan ini merupakan bagian dari dokumentasi resmi aplikasi Pixora Mobile.
> Untuk update terbaru, kunjungi website resmi kami.
