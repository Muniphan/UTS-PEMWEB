<?php
declare(strict_types=1);

namespace PustakaKu\Services;

use PustakaKu\Exceptions\RentalException;
use PustakaKu\Exceptions\ValidasiException;
use PustakaKu\Models\Media;
use PustakaKu\Repositories\{BookRepository, KategoriRepository};

/** Logika bisnis fitur admin: menambah, mengubah, dan menghapus buku di katalog. */
final class AdminService
{
    /** Lima gradasi merah (terang -> gelap) untuk sampul; warna teks dipilih agar kontrasnya >= 4,5:1. */
    public const WARNA = [
        ['nama' => 'Merah Muda', 'warna' => '#F7B8AD', 'teks' => '#17171A'],
        ['nama' => 'Merah Cerah', 'warna' => '#EE7060', 'teks' => '#17171A'],
        ['nama' => 'Merah', 'warna' => '#D2402B', 'teks' => '#FFFFFF'],
        ['nama' => 'Merah Tua', 'warna' => '#A32818', 'teks' => '#FFFFFF'],
        ['nama' => 'Merah Marun', 'warna' => '#6B170E', 'teks' => '#FAF5EA'],
    ];

    public const TIPE = [
        'ebook'   => 'E-book reguler',
        'premium' => 'E-book premium (biaya layanan 20%)',
    ];

    /** Nilai pilihan "kategori baru" pada dropdown kategori. */
    public const KATEGORI_BARU = '__baru__';
    /** Nilai pilihan "pertahankan warna sekarang" saat mengubah buku. */
    public const WARNA_TETAP = 'tetap';

    private const HARGA_MIN = 1000;
    private const HARGA_MAKS = 1000000;

    public function __construct(private BookRepository $books, private KategoriRepository $kategori)
    {
    }

    /**
     * @param array<string,mixed> $input data mentah dari form
     * @throws ValidasiException bila ada kolom yang tidak valid
     */
    public function tambahBuku(array $input): Media
    {
        $f = $this->validasi($input, null);
        $buku = Media::dariArray($f + ['id' => $this->books->buatId(), 'tersedia' => true]);
        $this->kategori->tambah($f['kategori']);
        $this->books->simpan($buku);

        return $buku;
    }

    /**
     * @param array<string,mixed> $input
     * @throws ValidasiException|RentalException
     */
    public function ubahBuku(string $id, array $input): Media
    {
        $lama = $this->books->temukan($id) ?? throw new RentalException('Buku yang akan diubah tidak ditemukan.');
        $f = $this->validasi($input, $lama);
        // Status tersedia/disewa tidak berubah lewat form; transaksi lama tetap memakai total yang sudah tercatat.
        $buku = Media::dariArray($f + ['id' => $id, 'tersedia' => $lama->tersedia()]);
        $this->kategori->tambah($f['kategori']);
        $this->books->simpan($buku);

        return $buku;
    }

    /** @return string judul buku yang dihapus */
    public function hapusBuku(string $id): string
    {
        $buku = $this->books->temukan($id) ?? throw new RentalException('Buku yang akan dihapus tidak ditemukan.');
        if (!$buku->tersedia()) {
            throw new RentalException("\"{$buku->getJudul()}\" sedang disewa member, jadi belum bisa dihapus.");
        }
        $this->books->hapus($id);

        return $buku->getJudul();
    }

    /** Nilai awal form dari buku yang sudah ada (untuk mode ubah). */
    public function isian(Media $b): array
    {
        $idx = self::indeksWarna($b->getWarna());

        return [
            'judul' => $b->getJudul(), 'penulis' => $b->getPenulis(),
            'kategori' => $b->getKategori(), 'kategori_baru' => '',
            'harga' => number_format($b->getHargaPer7Hari(), 0, ',', '.'),
            'tipe' => $b->tipe(), 'warna' => $idx === null ? self::WARNA_TETAP : (string) $idx,
        ];
    }

    /** Posisi warna di palet, atau null bila warna di luar palet (mis. data lama). */
    public static function indeksWarna(string $hex): ?int
    {
        foreach (self::WARNA as $i => $w) {
            if (strcasecmp($w['warna'], $hex) === 0) {
                return $i;
            }
        }
        return null;
    }

    /**
     * Validasi + normalisasi input form.
     *
     * @return array{judul:string,penulis:string,kategori:string,harga_per_7_hari:int,warna:string,teks:string,tipe:string}
     * @throws ValidasiException
     */
    private function validasi(array $input, ?Media $lama): array
    {
        $judul = $this->bersihkan($input['judul'] ?? '');
        $penulis = $this->bersihkan($input['penulis'] ?? '');
        $tipe = is_string($input['tipe'] ?? null) ? $input['tipe'] : '';
        $harga = self::parseHarga($input['harga'] ?? '');

        $galat = [];
        if ($judul === '') {
            $galat['judul'] = 'Judul wajib diisi.';
        } elseif (mb_strlen($judul) > 120) {
            $galat['judul'] = 'Judul maksimal 120 karakter.';
        }
        if ($penulis === '') {
            $galat['penulis'] = 'Penulis wajib diisi.';
        } elseif (mb_strlen($penulis) > 100) {
            $galat['penulis'] = 'Nama penulis maksimal 100 karakter.';
        }

        $kategori = $this->tentukanKategori($input, $galat);

        if ($harga === null || $harga < self::HARGA_MIN || $harga > self::HARGA_MAKS) {
            $galat['harga'] = 'Harga per 7 hari harus berupa angka antara Rp 1.000 dan Rp 1.000.000 (contoh: 70.000).';
        }
        if (!array_key_exists($tipe, self::TIPE)) {
            $galat['tipe'] = 'Pilih jenis e-book yang tersedia.';
        }

        $warna = $teks = '';
        $pilihWarna = is_string($input['warna'] ?? null) ? $input['warna'] : '';
        if ($lama !== null && $pilihWarna === self::WARNA_TETAP) {
            $warna = $lama->getWarna();
            $teks = $lama->getTeks();
        } else {
            $idx = filter_var($pilihWarna, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 0, 'max_range' => count(self::WARNA) - 1],
            ]);
            if ($idx === false) {
                $galat['warna'] = 'Pilih salah satu warna sampul.';
            } else {
                $warna = self::WARNA[$idx]['warna'];
                $teks = self::WARNA[$idx]['teks'];
            }
        }

        if (!isset($galat['judul']) && !isset($galat['penulis']) && $this->sudahAda($judul, $penulis, $lama?->getId())) {
            $galat['judul'] = 'Buku dengan judul dan penulis yang sama sudah ada di katalog.';
        }
        if ($galat) {
            throw new ValidasiException($galat);
        }

        return [
            'judul' => $judul, 'penulis' => $penulis, 'kategori' => $kategori,
            'harga_per_7_hari' => $harga, 'warna' => $warna, 'teks' => $teks, 'tipe' => $tipe,
        ];
    }

    /**
     * Kategori dipilih dari daftar, atau diketik baru bila memilih "Kategori baru".
     * Ejaan disamakan dengan kategori yang sudah ada (PHP, php, Php => PHP).
     *
     * @param array<string,string> $galat
     */
    private function tentukanKategori(array $input, array &$galat): string
    {
        $daftar = $this->kategori->semua($this->books->daftarKategori());
        $pilih = $this->bersihkan($input['kategori'] ?? '');

        if ($pilih === self::KATEGORI_BARU) {
            $nama = $this->bersihkan($input['kategori_baru'] ?? '');
            if ($nama === '') {
                $galat['kategori'] = 'Ketik nama kategori baru.';
            } elseif (mb_strlen($nama) > 40) {
                $galat['kategori'] = 'Nama kategori maksimal 40 karakter.';
            } else {
                return $this->cocokkan($nama, $daftar) ?? $nama;
            }
            return '';
        }
        if ($pilih === '') {
            $galat['kategori'] = 'Pilih kategori buku.';
            return '';
        }
        $ada = $this->cocokkan($pilih, $daftar);
        if ($ada === null) {
            $galat['kategori'] = 'Pilih kategori dari daftar, atau pilih "Kategori baru".';
            return '';
        }
        return $ada;
    }

    /** @param string[] $daftar */
    private function cocokkan(string $nama, array $daftar): ?string
    {
        foreach ($daftar as $k) {
            if (mb_strtolower($k) === mb_strtolower($nama)) {
                return $k;
            }
        }
        return null;
    }

    /**
     * Ubah teks harga menjadi angka. Menerima "70000", "70.000", atau "Rp 70.000";
     * titik hanya boleh sebagai pemisah ribuan (7.0000 ditolak).
     */
    public static function parseHarga(mixed $nilai): ?int
    {
        if (!is_string($nilai)) {
            return null;
        }
        $s = trim((string) preg_replace('/^Rp\.?\s*/i', '', trim($nilai)));
        if (preg_match('/^\d{1,3}(\.\d{3})+$/', $s) === 1) {
            $s = str_replace('.', '', $s);
        } elseif (preg_match('/^\d+$/', $s) !== 1) {
            return null;
        }
        return strlen($s) > 9 ? null : (int) $s;
    }

    /** Rapikan spasi ganda; nilai non-string dianggap kosong. */
    private function bersihkan(mixed $nilai): string
    {
        return is_string($nilai) ? trim((string) preg_replace('/\s+/u', ' ', $nilai)) : '';
    }

    private function sudahAda(string $judul, string $penulis, ?string $abaikanId): bool
    {
        foreach ($this->books->semua() as $b) {
            if ($b->getId() === $abaikanId) {
                continue;
            }
            if (mb_strtolower($b->getJudul()) === mb_strtolower($judul)
                && mb_strtolower($b->getPenulis()) === mb_strtolower($penulis)) {
                return true;
            }
        }
        return false;
    }
}
