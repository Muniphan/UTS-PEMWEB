<main class="wrap">
    <span class="tag">Dashboard Admin</span>
    <h1>Halo, <?= e(explode(' ', $admin->getNama())[0]) ?>. Saldo masuk dari penyewaan ada di sini.</h1>
    <p class="mut lead"><?= e($admin->getEmail()) ?></p>

    <section class="stats" aria-label="Ringkasan admin">
        <div class="card pad hl"><p>Saldo admin</p><strong><?= e(rupiah($admin->getSaldo())) ?></strong></div>
        <div class="card pad"><p>Transaksi berhasil</p><strong><?= count($transaksi) ?></strong></div>
        <div class="card pad"><p>Sedang disewa</p><strong><?= $aktif ?> buku</strong></div>
    </section>

    <div class="acts top"><a class="btn" href="index.php?page=admin-buku">+ Tambah buku baru</a></div>

    <h2>Transaksi masuk</h2>
    <div class="card scroll">
        <table>
            <thead><tr><th>Tanggal</th><th>Penyewa</th><th>E-book</th><th>Durasi</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>
            <?php if (!$transaksi): ?><tr><td colspan="6" class="mut">Belum ada transaksi.</td></tr><?php endif; ?>
            <?php foreach ($transaksi as $r): $b = $petaBuku[$r->getBookId()] ?? null; $m = $petaMember[$r->getMemberId()] ?? null; ?>
                <tr>
                    <td><?= e(tanggal($r->getMulai())) ?></td>
                    <td><strong><?= e($m?->getNama() ?? '-') ?></strong></td>
                    <td><?= e($b?->getJudul() ?? '-') ?></td>
                    <td><?= $r->getHari() ?> hari</td>
                    <td><?= e(rupiah($r->getTotal())) ?></td>
                    <td><?= $r->aktif() ? 'Aktif' : 'Selesai' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
