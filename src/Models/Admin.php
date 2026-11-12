<?php
declare(strict_types=1);

namespace PustakaKu\Models;

/** Admin pengelola katalog; saldonya bertambah setiap ada transaksi sewa/perpanjang yang berhasil. */
final class Admin extends User
{
    public function __construct(string $id, string $nama, string $email, string $passwordHash, private int $saldo = 0)
    {
        parent::__construct($id, $nama, $email, $passwordHash);
    }

    public function peran(): string { return 'admin'; }

    public function getSaldo(): int { return $this->saldo; }

    public function tambahSaldo(int $jumlah): void
    {
        if ($jumlah <= 0) {
            throw new \InvalidArgumentException('Jumlah penambahan saldo harus lebih dari nol.');
        }
        $this->saldo += $jumlah;
    }

    public static function dariArray(array $d): self
    {
        return new self($d['id'], $d['nama'], $d['email'], $d['password_hash'], (int) ($d['saldo'] ?? 0));
    }

    public function jsonSerialize(): array
    {
        return parent::jsonSerialize() + ['saldo' => $this->saldo];
    }
}
