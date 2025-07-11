
# 📄 README - ASFOUR (Website Survey)

## 🗂️ Struktur Folder

```
public_html/
├── futaridakeno.php       # Halaman login admin
├── dorimutaimu.php        # Dashboard admin
├── mirakuru.php           # Logout admin
├── aksi_pilih.php         # Kemungkinan pengelolaan data
├── export_excel.php       # Ekspor data survei ke Excel
├── hapus_survei.php       # Hapus data survei
├── proses.php             # Proses pengiriman form
├── terimakasih.html       # Halaman terima kasih setelah submit
├── index.html             # Halaman utama survei
├── style.css              # Gaya tampilan
├── asfourmy_asfour.sql    # Struktur dan data database
├── .htaccess, php.ini     # Konfigurasi server
├── images/                # Gambar dan ikon pendukung
├── music/                 # Musik latar
└── uploads/               # Folder untuk menyimpan file unggahan
```

## ⚙️ Instalasi

1. **Ekstrak** file `public_html.zip`.
2. **Upload ke hosting** (di dalam direktori `public_html/` jika menggunakan cPanel).
3. **Import database**:
   - Masuk ke **phpMyAdmin**.
   - Buat database, misalnya `asfour_db`.
   - Import file `asfourmy_asfour.sql`.
4. **Edit koneksi database** di file PHP yang sesuai (biasanya di awal file seperti `dorimutaimu.php` atau `proses.php`).

## 👥 Panduan Pengguna

### 💡 Pengguna (User)

1. Akses situs melalui `index.html`.
2. Isi form survei sesuai petunjuk.
3. Klik **submit**, data akan diproses lewat `proses.php`.
4. Akan diarahkan ke `terimakasih.html`.

## 🔐 Panduan Admin

### 🧭 Alur Akses Admin

- **Login:**  
  Akses `futaridakeno.php`  
  Masukkan kredensial:
  - **Username:** `admin`
  - **Password:** `admin123` *(ubah di database untuk keamanan)*

- **Dashboard/Admin Panel:**  
  Setelah login, diarahkan ke `dorimutaimu.php`  
  Fitur kemungkinan meliputi:
  - Melihat hasil survei
  - Menghapus entri (`hapus_survei.php`)
  - Mengekspor data (`export_excel.php`)
  
- **Logout:**  
  Klik tombol logout di dashboard atau akses langsung ke `mirakuru.php`

### ✅ Tips Keamanan

- Setelah instalasi, **ubah password admin** langsung di database (`tabel users`).
- Proteksi halaman admin dengan `.htaccess` (opsional).
- Gunakan SSL (https) untuk pengamanan data input.

## 📌 Catatan Teknis

- **Folder `uploads/` harus bisa ditulis (chmod 0777)** jika ada fitur unggah file.
- File `style.css` bisa dikembangkan untuk mempercantik tampilan.
- Folder `music/` otomatis memainkan file MP3 sebagai background (jika disetting).
- Jika muncul error 500, cek konfigurasi `.htaccess` atau `php.ini`.

## 🧪 Uji Coba Sistem

| Aksi                           | File yang Diakses        | Output                          |
|--------------------------------|---------------------------|----------------------------------|
| Isi survei                     | `index.html`              | Data masuk & halaman terima kasih |
| Login Admin                    | `futaridakeno.php`        | Masuk ke dashboard (`dorimutaimu.php`) |
| Lihat & Hapus Data Survei     | `dorimutaimu.php`         | Data table & aksi hapus         |
| Export Excel                   | `export_excel.php`        | Unduh file Excel                |
| Logout Admin                  | `mirakuru.php`            | Logout ke halaman login         |
