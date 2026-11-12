<?php
declare(strict_types=1);

namespace PustakaKu\Services;

use PustakaKu\Exceptions\RentalException;
use PustakaKu\Models\Member;
use PustakaKu\Models\Rental;
use PustakaKu\Repositories\{AdminRepository, BookRepository, MemberRepository, RentalRepository};

/** Logika bisnis simulasi transaksi penyewaan e-book. */
final class RentalService
{
    public const DURASI = [3, 7, 14];

    public function __construct(
        private BookRepository $books,
        private MemberRepository $members,
        private RentalRepository $rentals,
        private AdminRepository $admins,
    ) {
    }

    public function sewa(Member $member, string $bookId, int $hari): Rental
    {
        if (!in_array($hari, self::DURASI, true)) {
            throw new RentalException('Durasi sewa tidak valid.');
        }
        $buku = $this->books->temukan($bookId) ?? throw new RentalException('E-book tidak ditemukan.');
        if (!$buku->tersedia()) {
            throw new RentalException("\"{$buku->getJudul()}\" sedang disewa member lain.");
        }

        $total = $buku->hitungHarga($hari);   // polymorphism: harga tergantung jenis e-book
        $member->kurangiSaldo($total);
        $buku->setTersedia(false);

        $rental = new Rental($this->rentals->buatId(), $member->getId(), $buku->getId(), new \DateTimeImmutable('today'), $hari, $total);
        $this->rentals->simpan($rental);
        $this->books->simpan($buku);
        $this->catatPendapatan($total);
        $this->members->simpan($member);

        return $rental;
    }

    public function kembalikan(Member $member, string $rentalId): void
    {
        $rental = $this->milikMember($member, $rentalId);
        $rental->selesaikan();
        $buku = $this->books->temukan($rental->getBookId());
        if ($buku !== null) {
            $buku->setTersedia(true);
            $this->books->simpan($buku);
        }
        $this->rentals->simpan($rental);
    }

    public function perpanjang(Member $member, string $rentalId, int $hari = 7): void
    {
        $rental = $this->milikMember($member, $rentalId);
        $buku = $this->books->temukan($rental->getBookId()) ?? throw new RentalException('E-book tidak ditemukan.');
        $biaya = $buku->hitungHarga($hari);
        $member->kurangiSaldo($biaya);
        $rental->perpanjang($hari, $biaya);
        $this->rentals->simpan($rental);
        $this->catatPendapatan($biaya);
        $this->members->simpan($member);
    }

    /** Pembayaran member (sewa maupun perpanjang) masuk ke saldo admin utama. */
    private function catatPendapatan(int $jumlah): void
    {
        $admin = $this->admins->utama();
        if ($admin !== null && $jumlah > 0) {
            $admin->tambahSaldo($jumlah);
            $this->admins->simpan($admin);
        }
    }

    private function milikMember(Member $member, string $rentalId): Rental
    {
        $r = $this->rentals->temukan($rentalId);
        if ($r === null || $r->getMemberId() !== $member->getId() || !$r->aktif()) {
            throw new RentalException('Data penyewaan tidak ditemukan.');
        }
        return $r;
    }
}
