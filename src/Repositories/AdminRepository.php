<?php
declare(strict_types=1);

namespace PustakaKu\Repositories;

use PustakaKu\Models\Admin;

final class AdminRepository extends JsonRepository
{
    public function temukan(string $id): ?Admin
    {
        foreach ($this->load() as $row) {
            if ($row['id'] === $id) {
                return Admin::dariArray($row);
            }
        }
        return null;
    }

    public function temukanByEmail(string $email): ?Admin
    {
        foreach ($this->load() as $row) {
            if (strcasecmp($row['email'], $email) === 0) {
                return Admin::dariArray($row);
            }
        }
        return null;
    }

    /** Admin utama (data pertama): penerima saldo dari setiap transaksi berhasil. */
    public function utama(): ?Admin
    {
        $rows = $this->load();
        return $rows ? Admin::dariArray($rows[0]) : null;
    }

    public function simpan(Admin $a): void
    {
        $this->upsert($a, $a->getId());
    }
}
