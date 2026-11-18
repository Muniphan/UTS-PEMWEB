<?php $mode = $sunting ? 'ubah' : 'tambah'; $baru = $old['kategori'] === '__baru__'; ?>
<main class="wrap">
    <span class="tag"><?= $sunting ? 'Mode Penyuntingan' : 'Manajemen Buku' ?></span>
    <h1><?= $sunting ? 'Ubah data &ldquo;' . e($sunting->getJudul()) . '&rdquo;' : 'Tambah E-Book Baru ke Katalog' ?></h1>
    <p class="mut lead"><?= $sunting
        ? 'Perubahan harga hanya berlaku untuk penyewaan berikutnya; transaksi lama tetap memakai total yang sudah tercatat.'
        : 'Buku yang ditambahkan akan langsung tampil di katalog member dengan status siap disewa.' ?></p>

    <form class="card pad form cols" method="post" action="index.php?page=admin-buku<?= $sunting ? '&amp;edit=' . urlencode($sunting->getId()) : '' ?>" data-validate novalidate>
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="aksi" value="<?= $mode ?>">
        <?php if ($errors): ?><p class="err-box span2" role="alert">⚠️ Periksa kembali isian formulir yang ditandai merah.</p><?php endif; ?>

        <div class="field">
            <label for="judul">Judul E-Book</label>
            <input id="judul" name="judul" type="text" required maxlength="120" value="<?= e($old['judul']) ?>" placeholder="Contoh: Modern PHP 8 & Web Security">
            <small class="err"><?= e($errors['judul'] ?? '') ?></small>
        </div>
        <div class="field">
            <label for="penulis">Nama Penulis</label>
            <input id="penulis" name="penulis" type="text" required maxlength="100" value="<?= e($old['penulis']) ?>" placeholder="Contoh: Taylor Otwell">
            <small class="err"><?= e($errors['penulis'] ?? '') ?></small>
        </div>
        <div class="field">
            <label for="kategori">Kategori</label>
            <select id="kategori" name="kategori" data-kategori>
                <option value="">Pilih kategori…</option>
                <?php foreach ($kategoriList as $k): ?>
                <option value="<?= e($k) ?>"<?= $old['kategori'] === $k ? ' selected' : '' ?>><?= e($k) ?></option>
                <?php endforeach; ?>
                <option value="__baru__"<?= $baru ? ' selected' : '' ?>>+ Kategori baru…</option>
            </select>
            <div id="kategori-baru" <?= $baru ? '' : 'hidden' ?>>
                <label class="sr" for="kategori_baru">Nama kategori baru</label>
                <input id="kategori_baru" name="kategori_baru" type="text" maxlength="40" value="<?= e($old['kategori_baru']) ?>" placeholder="Ketik nama kategori baru">
            </div>
            <small class="err"><?= e($errors['kategori'] ?? '') ?></small>
        </div>
        <div class="field">
            <label for="harga">Harga Sewa (per 7 hari)</label>
            <div class="rp">
                <span aria-hidden="true">Rp</span>
                <input id="harga" name="harga" type="text" required inputmode="numeric" autocomplete="off" data-rupiah data-min="1000" data-max="1000000" maxlength="9" value="<?= e($old['harga']) ?>" placeholder="70.000">
            </div>
            <small class="err"><?= e($errors['harga'] ?? '') ?></small>
        </div>
        <div class="field span2">
            <label for="tipe">Tipe E-Book</label>
            <select id="tipe" name="tipe">
                <?php foreach ($tipe as $nilai => $label): ?>
                <option value="<?= e($nilai) ?>"<?= $old['tipe'] === $nilai ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <small class="err"><?= e($errors['tipe'] ?? '') ?></small>
        </div>
        <fieldset class="field span2">
            <legend>Warna Sampul Buku <span class="mut">(5 gradasi merah estetis)</span></legend>
            <div class="swatches">
                <?php foreach ($warna as $i => $w): ?>
                <label class="chip"><input type="radio" name="warna" value="<?= $i ?>"<?= $old['warna'] === (string) $i ? ' checked' : '' ?>><span class="sw" style="background:<?= e($w['warna']) ?>"></span><?= e($w['nama']) ?></label>
                <?php endforeach; ?>
                <?php if ($warnaTetap): ?>
                <label class="chip"><input type="radio" name="warna" value="tetap"<?= $old['warna'] === 'tetap' ? ' checked' : '' ?>><span class="sw" style="background:<?= e($warnaTetap['warna']) ?>"></span>Warna saat ini</label>
                <?php endif; ?>
            </div>
            <small class="err"><?= e($errors['warna'] ?? '') ?></small>
        </fieldset>
        <div class="acts end span2">
            <?php if ($sunting): ?><a class="btn o" href="index.php?page=admin-buku">Batal</a><?php endif; ?>
            <button class="btn" type="submit"><?= $sunting ? 'Simpan Perubahan' : '+ Simpan E-Book Baru' ?></button>
        </div>
    </form>

    <h2>📚 Daftar E-Book di Katalog (<?= count($items) ?>)</h2>
    <div class="card scroll">
        <table>
            <thead><tr><th>Sampul</th><th>Judul</th><th>Penulis</th><th>Kategori</th><th>Jenis</th><th>Harga / 7 hari</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($items as $b): ?>
                <tr<?= $sunting && $sunting->getId() === $b->getId() ? ' class="cur"' : '' ?>>
                    <td><span class="sw lg" style="background:<?= e($b->getWarna()) ?>" title="<?= e($b->getWarna()) ?>"></span></td>
                    <td><strong><?= e($b->getJudul()) ?></strong></td>
                    <td><?= e($b->getPenulis()) ?></td>
                    <td><span class="chip" style="min-height: 28px; padding: 0 10px; font-size: 12px; pointer-events: none;"><?= e($b->getKategori()) ?></span></td>
                    <td><?= $b->tipe() === 'premium' ? '<span class="badge" style="background: #FEF3C7; color: #B45309; border-color: #FCD34D;">⭐ Premium</span>' : '<span class="mut" style="font-size: 13px;">Reguler</span>' ?></td>
                    <td><strong style="color: var(--ink);"><?= e(rupiah($b->getHargaPer7Hari())) ?></strong></td>
                    <td><span class="<?= $b->tersedia() ? 'ok' : 'no' ?>"><?= $b->tersedia() ? 'Tersedia' : 'Disewa' ?></span></td>
                    <td>
                        <div class="acts">
                            <a class="btn o" style="min-height: 34px; padding: 0 14px; font-size: 12.5px;" href="index.php?page=admin-buku&amp;edit=<?= urlencode($b->getId()) ?>">Ubah</a>
                            <form method="post" action="index.php?page=admin-buku" data-konfirmasi="Hapus &quot;<?= e($b->getJudul()) ?>&quot; dari katalog? Tindakan ini tidak bisa dibatalkan.">
                                <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="book_id" value="<?= e($b->getId()) ?>">
                                <button class="btn o" style="min-height: 34px; padding: 0 14px; font-size: 12.5px; color: var(--coral-dd);" type="submit"<?= $b->tersedia() ? '' : ' disabled title="Sedang disewa, belum bisa dihapus"' ?>>Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
