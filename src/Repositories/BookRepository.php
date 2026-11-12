<?php
declare(strict_types=1);

namespace PustakaKu\Repositories;

use PustakaKu\Models\Media;

final class BookRepository extends JsonRepository
{
    /** @return Media[] */
    public function semua(): array
    {
        return array_map([Media::class, 'dariArray'], $this->load());
    }

    /** @return Media[] */
    public function cari(string $kata = '', string $kategori = ''): array
    {
        return array_values(array_filter($this->semua(), static function (Media $m) use ($kata, $kategori): bool {
            $cocokKata = $kata === '' || stripos($m->getJudul() . ' ' . $m->getPenulis(), $kata) !== false;
            return $cocokKata && ($kategori === '' || $m->getKategori() === $kategori);
        }));
    }

    public function temukan(string $id): ?Media
    {
        foreach ($this->semua() as $m) {
            if ($m->getId() === $id) {
                return $m;
            }
        }
        return null;
    }

    /** @return string[] */
    public function daftarKategori(): array
    {
        $k = array_unique(array_map(static fn (Media $m) => $m->getKategori(), $this->semua()));
        sort($k);
        return $k;
    }

    public function hapus(string $id): void
    {
        $this->save(array_filter($this->load(), static fn (array $row) => $row['id'] !== $id));
    }

    /** Id berikutnya berpola b<angka>, melanjutkan angka terbesar yang ada. */
    public function buatId(): string
    {
        $maks = 0;
        foreach ($this->load() as $row) {
            if (preg_match('/^b(\d+)$/', (string) $row['id'], $m)) {
                $maks = max($maks, (int) $m[1]);
            }
        }
        return 'b' . ($maks + 1);
    }

    public function simpan(Media $m): void
    {
        $this->upsert($m, $m->getId());
    }
}
