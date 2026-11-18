<main class="wrap">
    <span class="tag">Dashboard Member</span>
    <h1>Halo, <?= e(explode(' ', $member->getNama())[0]) ?>! <?= count($aktif) > 0 ? count($aktif) . ' e-book sedang kamu sewa.' : 'Siap membaca buku baru hari ini?' ?></h1>
    <p class="mut lead"><?= e($member->getEmail()) ?></p>

    <section class="stats" aria-label="Ringkasan akun">
        <div class="card pad hl"><p>Sedang disewa</p><strong><?= count($aktif) ?> <span style="font-size: 18px; font-weight: 600; color: inherit;">buku</span></strong></div>
        <div class="card pad"><p>Total transaksi</p><strong><?= count($riwayat) ?> <span style="font-size: 18px; font-weight: 600; color: inherit;">kali</span></strong></div>
        <div class="card pad"><p>Saldo dompet</p><strong><?= e(rupiah($member->getSaldo())) ?></strong></div>
    </section>

    <h2>📖 Sedang disewa</h2>
    <section class="rows">
        <?php if (!$aktif): ?>
        <div class="card pad" style="grid-column: 1 / -1; text-align: center; padding: 48px 24px; border: 1.5px dashed var(--border-light); background: var(--bg-surface);">
            <div style="font-size: 36px; margin-bottom: 10px;">📚</div>
            <p style="font-weight: 700; font-size: 16px; color: var(--ink); margin-bottom: 4px;">Belum ada e-book yang disewa</p>
            <p class="mut" style="margin-bottom: 20px;">Temukan buku favoritmu dan mulai tingkatkan wawasanmu sekarang.</p>
            <a class="btn" href="index.php?page=katalog">Jelajahi Katalog Buku &rarr;</a>
        </div>
        <?php endif; ?>
        <?php foreach ($aktif as $r): $b = $peta[$r->getBookId()] ?? null; $sisa = $r->sisaHari($sekarang); ?>
        <article class="card pad row bk">
            <div>
                <span class="<?= $sisa > 1 ? 'ok' : 'no' ?>"><?= $sisa >= 0 ? "Sisa $sisa hari" : 'Terlambat ' . abs($sisa) . ' hari' ?></span>
                <h3><?= e($b?->getJudul() ?? 'E-book dihapus') ?></h3>
                <p class="mut"><?= e($b?->getPenulis() ?? '-') ?> &middot; Jatuh tempo <?= e(tanggal($r->jatuhTempo())) ?></p>
            </div>
            <div class="acts">
                <form method="post" action="index.php?page=perpanjang"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="rental_id" value="<?= e($r->getId()) ?>"><button class="btn" type="submit">Perpanjang 7 hari</button></form>
                <form method="post" action="index.php?page=kembali"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="rental_id" value="<?= e($r->getId()) ?>"><button class="btn o" type="submit">Kembalikan</button></form>
            </div>
        </article>
        <?php endforeach; ?>
    </section>

    <h2>⏱️ Riwayat transaksi</h2>
    <div class="card scroll">
        <table>
            <thead><tr><th>Tanggal</th><th>E-book</th><th>Durasi</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>
            <?php if (!$riwayat): ?>
                <tr><td colspan="5" style="text-align: center; padding: 32px; color: var(--mut);">Belum ada riwayat transaksi.</td></tr>
            <?php endif; ?>
            <?php foreach ($riwayat as $r): $b = $peta[$r->getBookId()] ?? null; ?>
                <tr>
                    <td><?= e(tanggal($r->getMulai())) ?></td>
                    <td><strong><?= e($b?->getJudul() ?? '-') ?></strong></td>
                    <td><?= $r->getHari() ?> hari</td>
                    <td><strong style="color: var(--ink);"><?= e(rupiah($r->getTotal())) ?></strong></td>
                    <td><span class="<?= $r->aktif() ? 'ok' : 'no' ?>"><?= $r->aktif() ? 'Aktif' : 'Selesai' ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
