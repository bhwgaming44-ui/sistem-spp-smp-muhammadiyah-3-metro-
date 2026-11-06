# Demo Offline E-SPP — untuk HP tanpa Laravel/XAMPP

Folder ini berisi **satu file HTML mandiri** yang bisa dibuka langsung di HP
untuk uji coba di depan dosen, **tanpa** menjalankan Laravel, MySQL, XAMPP,
atau server apa pun.

| File | Fungsi |
|---|---|
| `spp-offline.html` | **File yang dibuka di HP.** Berisi seluruh tampilan, CSS, JavaScript, dan backend tiruan. |
| `mock-backend.js` | Sumber backend tiruan (data contoh & simulasi pembayaran). |
| `build-demo.py` | Membuat ulang `spp-offline.html` dari `resources/views/welcome.blade.php`. |

> Pembayaran Midtrans pada file ini **disimulasikan**. Tidak ada uang yang
> berpindah dan tidak ada request ke server Midtrans.

## Cara membuka di HP

### Cara 1 — Kirim file ke HP (paling mudah, tanpa internet)

1. Kirim `spp-offline.html` ke HP:
   - WhatsApp → chat "Pesan ke diri sendiri" → lampirkan sebagai **Dokumen**,
   - atau Google Drive, atau kabel USB ke folder `Download`.
2. Simpan file di folder `Download`.
3. Buka aplikasi **Files / Pengelola Berkas** di HP.
4. Ketuk `spp-offline.html` → pilih **Chrome**.
   - Jika tidak ada pilihan Chrome: buka Chrome, ketik di address bar
     `file:///sdcard/Download/spp-offline.html`
     (pada beberapa HP path-nya `file:///storage/emulated/0/Download/spp-offline.html`).
5. Login memakai tombol **Akun Uji Coba** di halaman login.

### Cara 2 — Buka di laptop dulu

Klik dua kali `spp-offline.html`. File langsung terbuka di browser.

### Cara 3 — Hosting statis (opsional)

Jika malas memindahkan file, unggah **hanya** `spp-offline.html` ke layanan
hosting statis gratis (Netlify Drop, GitHub Pages, Cloudflare Pages), lalu buka
link-nya di HP. Ini hanya file demo, bukan aplikasi Laravel lengkap.

## Akun demo

| Peran | Username | Password |
|---|---|---|
| Siswa | `0081234567` | `password123` |
| Siswa | `0087654321` | `password123` |
| Wali murid | `ortu001` | `password123` |
| Petugas / Admin | `admin` | `admin123` |
| Bendahara | `bendahara` | `bendahara123` |
| Kepala Sekolah | `kepsek` | `kepsek123` |
| Guru (30 akun) | `guru001` … `guru030` | `guru123` |
| Wali Kelas (9 akun) | `wali7a` … `wali9c` | `wali123` |

Selain akun di atas, tersedia **261 akun siswa** (9 kelas × 29 siswa).
Username siswa = NISN, misalnya `0081000101` (7A) sampai `0081000929` (9C),
dengan password `password123`. Daftar lengkapnya bisa dibuka petugas melalui
menu **🔑 Akun Siswa & Wali Kelas**.

Bisa juga klik kartu **Akun Uji Coba (Klik Langsung Masuk)** di halaman login.

## Alur demo pembayaran (disimulasikan)

1. Login sebagai siswa (`0081234567` / `password123`).
2. Buka menu **Tagihan** atau **Bayar SPP**.
3. Centang sendiri tagihan yang ingin dibayar (boleh sebagian, boleh juga
   **tidak memilih sama sekali**) → klik **BAYAR**.
4. Langsung muncul jendela pembayaran (mirip Snap Midtrans), tanpa verifikasi
   selfie dan tanpa akses kamera. Metode pembayaran bebas dipilih di jendela
   tersebut.
5. Pilih salah satu dari **6 metode** yang langsung terlihat:
   **GoPay QRIS, Card Payment, ShopeePay QRIS, OVO, DANA, atau Bank Virtual
   Account (BSI/Mandiri/BRI/BCA)**.
6. Klik **✅ Simulasi Pembayaran Berhasil (Snap)**.
7. Tunggu ±3 detik → status berubah **Lunas**, riwayat bertambah, dan
   kuitansi otomatis terbuka.

Menutup jendela pembayaran **tidak** memunculkan notifikasi apa pun.

## Yang perlu diketahui saat demo

- Menu utama sekarang berada **di sisi kiri** sebagai sidebar setinggi layar yang
  menempel tepi kiri (mulai di bawah header sampai bawah layar), bukan lagi bar
  horizontal di atas. Sidebar tetap memakai label di HP (menyempit jadi
  ±118–132px) dan area konten mengisi seluruh sisa lebar layar tanpa batas
  maksimum.
- Data siswa, tagihan, dan petugas adalah **data contoh** di dalam file.
  Perubahan (tambah/edit/hapus) hanya tersimpan selama halaman terbuka.
- Tersedia **261 siswa** (9 kelas × 29 siswa). Menu **🏫 Siswa per Kelas (29)**
  menampilkan data per kelas, sedangkan menu **👥 Data Siswa & SPP** memiliki
  filter kelas dan pencarian tagihan real-time (AJAX) tanpa reload halaman.
- Menu **👔 Akun Petugas & Guru** memisahkan akun petugas inti, 30 guru, dan
  kepala sekolah. Menu **🔑 Akun Siswa & Wali Kelas** menampilkan akun siswa
  per kelas dan 9 akun wali kelas.
- Menu **🌐 Publikasi Online** tetap ada, tetapi statusnya menunjukkan
  `DEMO-OFFLINE` karena memang tidak ada server.
- Font Google dan QR publikasi memerlukan internet. Jika offline, aplikasi
  tetap berjalan dengan font bawaan HP.
- Dropdown **tahun ajaran** langsung terisi 46 pilihan (2000/2001–2045/2046)
  sejak form dibuka, jadi bisa langsung dipilih tanpa menunggu server. Pilihan
  petugas tidak berubah saat request data selesai atau saat membuka ulang menu.
- Data tagihan yang dibuat petugas, status pembayaran, dan riwayat transaksi
  **disimpan di browser** (`localStorage`), jadi tidak hilang saat halaman
  di-refresh atau ditutup lalu dibuka lagi.
- Untuk mengosongkan data demo dan kembali ke data awal, buka pengaturan situs
  di browser lalu hapus data situs (`localStorage`) untuk halaman ini.
- Pembayaran **tidak** meminta verifikasi selfie atau akses kamera. Alur
  langsung menuju jendela pembayaran setelah siswa mencentang tagihan.
- Tidak ada pemilihan otomatis (1-click). Semua tagihan tampil, termasuk
  tagihan lama dan beberapa tagihan pada bulan yang sama, sehingga tagihan lama
  tidak hilang saat petugas menerbitkan tagihan baru.
- Saat pembayaran berhasil, hanya tagihan yang dicentang yang menjadi lunas.
  Tagihan lain tetap tampil dan tetap bisa dibayar.
- Jendela pembayaran dibuat ringkas supaya **seluruh metode langsung terlihat**
  di layar HP. Jika layar sangat pendek, daftar metode tetap bisa digulir.
- Pada aplikasi Laravel, daftar metode Snap diambil otomatis dari dashboard
  Midtrans (tidak lagi dikunci daftar manual), sehingga QRIS, GoPay, ShopeePay,
  OVO, DANA, kartu, dan Virtual Account yang aktif akan tampil.
- `localStorage` dipakai untuk menyimpan sesi login. Jika browser membatasi
  `localStorage` pada `file://`, mock menyediakan penyimpanan sementara.

## Membuat ulang file demo

Setelah `resources/views/welcome.blade.php` diubah:

```powershell
cd "C:\Users\ASUS\Documents\file skrpsi\spp-app"
python demo\build-demo.py
```

Perintah di atas menulis ulang `demo/spp-offline.html`.

## Batasan

- Tidak bisa memakai Midtrans sandbox/production sungguhan, karena pembuatan
  Snap token membutuhkan **Server Key** yang hanya boleh ada di server.
- Tidak ada database sungguhan; semua data hilang saat halaman ditutup.
- Untuk aplikasi online sesungguhnya (multi-device, data tersimpan), gunakan
  panduan di `../DEPLOY.md`.
