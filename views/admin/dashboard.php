<main class="wrap">
    <span class="tag">Dashboard Pengelola</span>
    <h1>Halo, <?= e(explode(' ', $admin->getNama())[0]) ?>! Ringkasan pendapatan &amp; transaksi terkini.</h1>
    <p class="mut lead"><?= e($admin->getEmail()) ?></p>

    <section class="stats" aria-label="Ringkasan admin">
        <div class="card pad hl">
            <p>Total Saldo Masuk</p>
            <strong><?= e(rupiah($admin->getSaldo())) ?></strong>
        </div>
        <div class="card pad">
            <p>Transaksi Berhasil</p>
            <strong><?= count($transaksi) ?> <span style="font-size: 18px; font-weight: 600; color: inherit;">kali</span></strong>
        </div>
        <div class="card pad">
            <p>Buku Sedang Disewa</p>
            <strong><?= $aktif ?> <span style="font-size: 18px; font-weight: 600; color: inherit;">buku</span></strong>
        </div>
    </section>

    <div class="acts top" style="display: flex; justify-content: space-between; align-items: center; margin-top: 36px; margin-bottom: 16px;">
        <h2 style="margin: 0;">📊 Transaksi Penyewaan Masuk</h2>
        <a class="btn" href="index.php?page=admin-buku">+ Tambah Buku Baru</a>
    </div>

    <div class="card scroll">
        <table>
            <thead><tr><th>Tanggal</th><th>Penyewa</th><th>E-book</th><th>Durasi</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>
            <?php if (!$transaksi): ?>
                <tr><td colspan="6" style="text-align: center; padding: 32px; color: var(--mut);">Belum ada transaksi sewa yang tercatat.</td></tr>
            <?php endif; ?>
            <?php foreach ($transaksi as $r): $b = $petaBuku[$r->getBookId()] ?? null; $m = $petaMember[$r->getMemberId()] ?? null; ?>
                <tr>
                    <td><?= e(tanggal($r->getMulai())) ?></td>
                    <td><strong><?= e($m?->getNama() ?? '-') ?></strong></td>
                    <td><?= e($b?->getJudul() ?? '-') ?></td>
                    <td><?= $r->getHari() ?> hari</td>
                    <td><strong style="color: var(--ink);"><?= e(rupiah($r->getTotal())) ?></strong></td>
                    <td><span class="<?= $r->aktif() ? 'ok' : 'no' ?>"><?= $r->aktif() ? 'Aktif' : 'Selesai' ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
