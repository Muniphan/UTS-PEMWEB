<?php
declare(strict_types=1);

namespace PustakaKu\Exceptions;

/** Kesalahan validasi form; membawa pesan galat per kolom. */
final class ValidasiException extends \RuntimeException
{
    /** @param array<string,string> $galat nama kolom => pesan */
    public function __construct(private array $galat)
    {
        parent::__construct('Data yang dikirim belum valid.');
    }

    /** @return array<string,string> */
    public function galat(): array
    {
        return $this->galat;
    }
}
