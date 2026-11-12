<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/src/autoload.php';

use PustakaKu\Exceptions\{RentalException, ValidasiException};
use PustakaKu\Repositories\{AdminRepository, BookRepository, KategoriRepository, MemberRepository, RentalRepository};
use PustakaKu\Services\{AdminService, RentalService};
use PustakaKu\Support\View;

session_start();
$_SESSION['csrf'] ??= bin2hex(random_bytes(16));

$books = new BookRepository(BASE_PATH . '/data/books.json');
$members = new MemberRepository(BASE_PATH . '/data/members.json');
$rentals = new RentalRepository(BASE_PATH . '/data/rentals.json');
$admins = new AdminRepository(BASE_PATH . '/data/admins.json');
$service = new RentalService($books, $members, $rentals, $admins);
$kategoriRepo = new KategoriRepository(BASE_PATH . '/data/kategori.json');
$adminService = new AdminService($books, $kategoriRepo);

$page = $_GET['page'] ?? 'dashboard';
$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$csrf = $_SESSION['csrf'];
if ($isPost && !hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) {
    http_response_code(419);
    exit('Token keamanan tidak valid. Muat ulang halaman.');
}

$member = isset($_SESSION['member_id']) ? $members->temukan($_SESSION['member_id']) : null;
$admin = isset($_SESSION['admin_id']) ? $admins->temukan($_SESSION['admin_id']) : null;

if ($page === 'logout') {
    session_destroy();
    header('Location: index.php?page=login');
    exit;
}

if ($page === 'login') {
    if ($admin) {
        redirect('admin');
    }
    if ($member) {
        redirect('dashboard');
    }
    $errors = [];
    $email = '';
    if ($isPost) {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid.';
        }
        if (strlen($password) < 6) {
            $errors['password'] = 'Kata sandi minimal 6 karakter.';
        }
        if (!$errors) {
            $a = $admins->temukanByEmail($email);
            if ($a !== null && $a->verifikasiPassword($password)) {
                session_regenerate_id(true);
                unset($_SESSION['member_id']);
                $_SESSION['admin_id'] = $a->getId();
                redirect('admin');
            }
            $m = $members->temukanByEmail($email);
            if ($m !== null && $m->verifikasiPassword($password)) {
                session_regenerate_id(true);
                unset($_SESSION['admin_id']);
                $_SESSION['member_id'] = $m->getId();
                redirect('dashboard');
            }
            $errors['umum'] = 'Email atau kata sandi salah.';
        }
    }
    View::render('login', compact('page', 'member', 'csrf', 'errors', 'email'));
    exit;
}

if (!$member && !$admin) {
    redirect('login');
}

// ---- Area admin: saldo & transaksi, tambah buku ----
if ($admin) {
    if ($page === 'admin-buku') {
        $errors = [];
        // Mode ubah: ?page=admin-buku&edit=<id> (juga dipertahankan saat form dikirim)
        $editId = (string) ($_GET['edit'] ?? '');
        $sunting = $editId !== '' ? $books->temukan($editId) : null;
        if ($editId !== '' && $sunting === null) {
            flash('err', 'Buku yang akan diubah tidak ditemukan.');
            redirect('admin-buku');
        }
        $old = $sunting
            ? $adminService->isian($sunting)
            : ['judul' => '', 'penulis' => '', 'kategori' => '', 'kategori_baru' => '', 'harga' => '', 'tipe' => 'ebook', 'warna' => '2'];

        if ($isPost) {
            $aksi = (string) ($_POST['aksi'] ?? 'tambah');
            try {
                if ($aksi === 'hapus') {
                    $judul = $adminService->hapusBuku((string) ($_POST['book_id'] ?? ''));
                    flash('ok', 'Buku "' . $judul . '" berhasil dihapus dari katalog.');
                    redirect('admin-buku');
                }
                foreach (array_keys($old) as $kolom) {
                    $old[$kolom] = is_string($_POST[$kolom] ?? null) ? $_POST[$kolom] : '';
                }
                if ($aksi === 'ubah' && $sunting !== null) {
                    $buku = $adminService->ubahBuku($sunting->getId(), $_POST);
                    flash('ok', 'Buku "' . $buku->getJudul() . '" berhasil diperbarui.');
                } else {
                    $buku = $adminService->tambahBuku($_POST);
                    flash('ok', 'Buku "' . $buku->getJudul() . '" berhasil ditambahkan ke katalog.');
                }
                redirect('admin-buku');
            } catch (ValidasiException $ex) {
                $errors = $ex->galat();
            } catch (RentalException $ex) {
                flash('err', $ex->getMessage());
                redirect('admin-buku');
            }
        }
        $items = array_reverse($books->semua());   // buku terbaru di atas
        $kategoriList = $kategoriRepo->semua($books->daftarKategori());
        $warna = AdminService::WARNA;
        $tipe = AdminService::TIPE;
        // Warna lama di luar palet merah: tampilkan sebagai pilihan "warna saat ini" agar tidak berubah tanpa sengaja.
        $warnaTetap = ($sunting && AdminService::indeksWarna($sunting->getWarna()) === null)
            ? ['warna' => $sunting->getWarna(), 'teks' => $sunting->getTeks()]
            : null;
        View::render('admin/buku', compact('page', 'admin', 'csrf', 'errors', 'old', 'items', 'kategoriList', 'warna', 'tipe', 'sunting', 'warnaTetap'));
        exit;
    }
    if ($page !== 'admin') {
        redirect('admin');
    }

    $petaBuku = [];
    foreach ($books->semua() as $b) {
        $petaBuku[$b->getId()] = $b;
    }
    $petaMember = [];
    foreach ($members->semua() as $m) {
        $petaMember[$m->getId()] = $m;
    }
    $transaksi = $rentals->semua();
    $aktif = count(array_filter($transaksi, static fn ($r) => $r->aktif()));
    View::render('admin/dashboard', compact('page', 'admin', 'csrf', 'transaksi', 'aktif', 'petaBuku', 'petaMember'));
    exit;
}

// ---- Area member ----
if (str_starts_with((string) $page, 'admin')) {
    flash('err', 'Halaman tersebut khusus admin.');
    redirect('dashboard');
}

try {
    if ($isPost && $page === 'sewa') {
        $r = $service->sewa($member, (string) ($_POST['book_id'] ?? ''), (int) ($_POST['hari'] ?? 0));
        flash('ok', 'Penyewaan berhasil. Jatuh tempo ' . tanggal($r->jatuhTempo()) . '.');
        redirect('dashboard');
    }
    if ($isPost && $page === 'kembali') {
        $service->kembalikan($member, (string) ($_POST['rental_id'] ?? ''));
        flash('ok', 'E-book berhasil dikembalikan.');
        redirect('dashboard');
    }
    if ($isPost && $page === 'perpanjang') {
        $service->perpanjang($member, (string) ($_POST['rental_id'] ?? ''));
        flash('ok', 'Masa sewa diperpanjang 7 hari.');
        redirect('dashboard');
    }
} catch (RentalException $ex) {
    flash('err', $ex->getMessage());
    redirect($page === 'sewa' ? 'katalog' : 'dashboard');
}

if ($page === 'katalog') {
    $q = trim((string) ($_GET['q'] ?? ''));
    $kategori = (string) ($_GET['kategori'] ?? '');
    $items = $books->cari($q, $kategori);
    $kategoriList = $books->daftarKategori();
    View::render('katalog', compact('page', 'member', 'csrf', 'q', 'kategori', 'items', 'kategoriList'));
    exit;
}

$peta = [];
foreach ($books->semua() as $b) {
    $peta[$b->getId()] = $b;
}
$riwayat = $rentals->milik($member->getId());
$aktif = array_values(array_filter($riwayat, static fn ($r) => $r->aktif()));
$sekarang = new DateTimeImmutable('today');
View::render('dashboard', compact('page', 'member', 'csrf', 'peta', 'riwayat', 'aktif', 'sekarang'));
