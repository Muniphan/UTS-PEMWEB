# Pustaka.ku – Katalog Media & Simulasi Sewa E-Book

Prototipe UTS Pemrograman Web (Ganjil 2026/2027). Front-end HTML/CSS/JS + backend **PHP 8 OOP tanpa framework**, penyimpanan **JSON**.

## Menjalankan
Butuh PHP 8.1+ (`php -v`). Dari folder proyek:

    php -S localhost:8000 -t public

atau di VS Code: `Ctrl+Shift+B` (task *Jalankan server PHP*). Buka http://localhost:8000

Akun demo member: `rani.putri@mail.id` / `pustaka123` (juga `budi.santoso@mail.id` / `budi12345`).

Akun demo admin: `admin@pustaka.id` / `admin12345` (setelah login langsung diarahkan ke Dashboard Admin).

## Struktur
    public/            index.php (front controller), assets/css, assets/js
    src/Contracts/     Rentable (interface)
    src/Models/        Media (abstract) → EBook → EBookPremium; User (abstract) → Member, Admin; Rental
    src/Repositories/  JsonRepository (abstract) → Book/Member/Admin/Kategori/RentalRepository
    src/Services/      RentalService (logika sewa, kembali, perpanjang; mengisi saldo admin), AdminService (tambah, ubah, hapus buku)
    src/Exceptions/    RentalException, ValidasiException
    src/Support/       View (render & escape)
    views/             layout, login, dashboard, katalog (+ modal); views/admin/ dashboard & buku
    data/              books.json, members.json, admins.json, kategori.json, rentals.json

## Fitur admin
- **Login admin**: form login yang sama dipakai member dan admin. Email admin dicari di `data/admins.json` lebih dulu; jika cocok, sesi disimpan sebagai admin dan diarahkan ke `index.php?page=admin`.
- **Saldo admin**: setiap transaksi berhasil (sewa baru atau perpanjang) menambah saldo admin utama (data pertama di `admins.json`) sebesar total yang dibayar member. Pengembalian e-book tidak mengubah saldo. Saldo awal 108.000 sama dengan jumlah enam transaksi contoh di `rentals.json`.
- **Dashboard Admin** (`?page=admin`): saldo, jumlah transaksi, jumlah buku yang sedang disewa, dan tabel transaksi berisi nama penyewa, e-book, durasi, total, dan status.
- **Kelola Buku** (`?page=admin-buku`): tambah, ubah, dan hapus buku.
  - **Kategori fleksibel**: dipilih dari dropdown (daftar di `data/kategori.json`, sudah berisi PHP, JavaScript, Python, Java, Basis Data, Keamanan Siber, Kewirausahaan, dll.) atau pilih *+ Kategori baru…* lalu ketik namanya; kategori baru otomatis tersimpan dan ejaannya disamakan dengan yang sudah ada (php → PHP).
  - **Harga bertitik**: kolom harga otomatis berformat ribuan saat diketik (70000 → 70.000). Server menerima `70000`, `70.000`, atau `Rp 70.000`; rentang Rp 1.000 sampai Rp 1.000.000.
  - **Warna sampul**: 5 gradasi merah (Merah Muda, Merah Cerah, Merah, Merah Tua, Merah Marun) dengan warna teks yang kontrasnya aman.
  - **Ubah**: tombol *Ubah* di daftar membuka form yang sudah terisi. Perubahan harga hanya berlaku untuk sewa berikutnya.
  - **Hapus**: ada konfirmasi dulu; buku yang sedang disewa tidak bisa dihapus.
  - Validasi di JS dan PHP; judul + penulis yang sama dengan buku lain ditolak.
- **Pembatasan akses**: halaman `admin*` hanya untuk admin; member yang membukanya diarahkan ke dashboard member, dan admin yang membuka halaman member diarahkan ke dashboard admin.

## Pemetaan rubrik
- **Front-End (30)**: elemen semantik (header/nav/main/section/article/dialog/fieldset), validasi form login (JS + PHP), layout responsif, JS: modal `<dialog>`, hitung harga & jatuh tempo, pencarian instan, filter kategori.
- **PHP OOP (40)**: namespace `PustakaKu\…` + autoloader PSR-4; encapsulation (properti private + getter/setter); inheritance (`Media`→`EBook`→`EBookPremium`, `User`→`Member`/`Admin`, `JsonRepository`); polymorphism (`hitungHarga()` berbeda per kelas); interface `Rentable`; exception khusus.
- **Struktur (30)**: kode di `src/`, satu kelas satu berkas, view terpisah dari logika.

Data di `data/*.json` berubah saat transaksi; salin ulang berkas aslinya jika ingin mengembalikan data awal.
