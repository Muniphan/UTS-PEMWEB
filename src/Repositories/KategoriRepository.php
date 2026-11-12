<?php
declare(strict_types=1);

namespace PustakaKu\Repositories;

/** Daftar kategori buku yang tersimpan (data/kategori.json); admin bisa menambah kategori baru. */
final class KategoriRepository extends JsonRepository
{
    /** @return string[] kategori yang tersimpan, urutan asli berkas */
    public function tersimpan(): array
    {
        return array_values(array_filter($this->load(), 'is_string'));
    }

    /**
     * Semua kategori (tersimpan + yang sudah dipakai buku), tanpa duplikat, urut abjad.
     *
     * @param string[] $dipakai kategori yang sudah dipakai buku
     * @return string[]
     */
    public function semua(array $dipakai = []): array
    {
        $unik = [];
        foreach (array_merge($this->tersimpan(), $dipakai) as $k) {
            $unik[mb_strtolower($k)] ??= $k;
        }
        $daftar = array_values($unik);
        sort($daftar, SORT_NATURAL | SORT_FLAG_CASE);
        return $daftar;
    }

    public function tambah(string $nama): void
    {
        $daftar = $this->tersimpan();
        foreach ($daftar as $k) {
            if (mb_strtolower($k) === mb_strtolower($nama)) {
                return;
            }
        }
        $daftar[] = $nama;
        $this->save($daftar);
    }
}
