<main class="wrap">
    <span class="tag">Katalog Media Digital</span>
    <div class="head">
        <div>
            <h1>E-Book Pemrograman Web Terpilih.</h1>
            <p class="mut">Pinjam ilmu tanpa antre, tingkatkan skill kapan saja.</p>
        </div>
        <form class="search" method="get" action="index.php" role="search">
            <input type="hidden" name="page" value="katalog">
            <?php if ($kategori !== ''): ?><input type="hidden" name="kategori" value="<?= e($kategori) ?>"><?php endif; ?>
            <label class="sr" for="q">Cari judul atau penulis</label>
            <input id="q" type="search" name="q" value="<?= e($q) ?>" placeholder="🔍 Cari judul atau penulis...">
            <button class="btn" type="submit">Cari</button>
        </form>
    </div>

    <nav class="chips" aria-label="Filter kategori">
        <?php foreach (['' => '✨ Semua'] + array_combine($kategoriList, $kategoriList) as $k => $label): ?>
            <a class="chip<?= (string) $k === $kategori ? ' on' : '' ?>" href="index.php?page=katalog&amp;kategori=<?= urlencode((string) $k) ?>&amp;q=<?= urlencode($q) ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </nav>

    <section class="grid" id="grid" aria-label="Daftar e-book">
        <?php foreach ($items as $b): $ada = $b->tersedia(); ?>
        <article class="card bk book" data-cari="<?= e(strtolower($b->getJudul() . ' ' . $b->getPenulis())) ?>">
            <div class="cover" style="background:<?= e($b->getWarna()) ?>;color:<?= e($b->getTeks()) ?>">
                <h3><?= e($b->getJudul()) ?></h3>
            </div>
            <div class="body">
                <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                    <span class="<?= $ada ? 'ok' : 'no' ?>"><?= $ada ? 'Tersedia' : 'Disewa' ?></span>
                    <?php if ($b->tipe() === 'premium'): ?>
                        <span class="badge" style="background: #FEF3C7; color: #B45309; border-color: #FCD34D;">⭐ Premium</span>
                    <?php endif; ?>
                </div>
                <p class="jd"><?= e($b->getJudul()) ?></p>
                <p class="mut"><?= e($b->getPenulis()) ?> &middot; <?= e($b->getKategori()) ?></p>
                <div class="buy">
                    <div>
                        <strong><?= e(rupiah($b->hitungHarga(7))) ?></strong>
                        <span class="mut" style="font-size: 12px;"> / 7 hari</span>
                    </div>
                    <button type="button" class="btn sewa" <?= $ada ? '' : 'disabled' ?>
                        data-id="<?= e($b->getId()) ?>" data-judul="<?= e($b->getJudul()) ?>" data-penulis="<?= e($b->getPenulis()) ?>"
                        data-p3="<?= $b->hitungHarga(3) ?>" data-p7="<?= $b->hitungHarga(7) ?>" data-p14="<?= $b->hitungHarga(14) ?>"><?= $ada ? 'Sewa Buku' : 'Sedang Disewa' ?></button>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </section>

    <div id="kosong" class="empty" <?= $items ? 'hidden' : '' ?>>
        <p style="font-size: 32px; margin-bottom: 8px;">🔍</p>
        <p style="font-weight: 700; color: var(--ink); margin-bottom: 4px;">E-book tidak ditemukan</p>
        <p class="mut">Coba kata kunci lain atau pilih kategori "Semua".</p>
    </div>
</main>

<dialog id="modal-sewa" data-saldo="<?= $member->getSaldo() ?>" aria-labelledby="m-judul">
    <form method="post" action="index.php?page=sewa" class="modal">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="book_id" id="m-id">
        <div class="mh">
            <span class="tag">Simulasi Sewa E-Book</span>
            <button type="button" class="btn o sq" data-close aria-label="Tutup">✕</button>
        </div>
        <h2 id="m-judul" style="margin: 8px 0 4px; font-size: 22px;"></h2>
        <p class="mut" id="m-penulis" style="margin-bottom: 16px;"></p>
        
        <fieldset>
            <legend>Pilih Durasi Sewa:</legend>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <label class="chip"><input type="radio" name="hari" value="3"> ⚡ 3 hari</label>
                <label class="chip"><input type="radio" name="hari" value="7" checked> 📅 7 hari (Populer)</label>
                <label class="chip"><input type="radio" name="hari" value="14"> 🚀 14 hari</label>
            </div>
        </fieldset>

        <dl class="sum">
            <div><dt>Harga sewa</dt><dd id="m-harga"></dd></div>
            <div><dt>Jatuh tempo</dt><dd id="m-tempo"></dd></div>
            <div class="tot"><dt>Total Biaya</dt><dd id="m-total"></dd></div>
            <p class="mut" id="m-saldo" style="font-size: 13px; margin-top: 4px;"></p>
        </dl>

        <div class="acts end">
            <button type="button" class="btn o" data-close>Batal</button>
            <button type="submit" class="btn" id="m-ok">Konfirmasi &amp; Sewa Sekarang</button>
        </div>
    </form>
</dialog>
