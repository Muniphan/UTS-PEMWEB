<main class="wrap narrow">
    <div style="text-align: center; margin-bottom: 24px;">
        <span class="tag">Portal Masuk</span>
        <h1>Selamat Datang di Pustaka<span class="accent">.ku</span></h1>
        <p class="mut">Akses ribuan wawasan pemrograman web dalam genggamanmu.</p>
    </div>
    <form class="card pad form" method="post" action="index.php?page=login" data-validate novalidate>
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <?php if (isset($errors['umum'])): ?><p class="err-box" role="alert"><?= e($errors['umum']) ?></p><?php endif; ?>
        <div class="field">
            <label for="email">Alamat Email</label>
            <input id="email" name="email" type="email" required value="<?= e($email) ?>" placeholder="rani.putri@mail.id" autocomplete="email">
            <small class="err"><?= e($errors['email'] ?? '') ?></small>
        </div>
        <div class="field">
            <label for="password">Kata Sandi</label>
            <input id="password" name="password" type="password" required minlength="6" placeholder="Minimal 6 karakter" autocomplete="current-password">
            <small class="err"><?= e($errors['password'] ?? '') ?></small>
        </div>
        <button class="btn" type="submit" style="width: 100%; min-height: 46px; font-size: 15px;">Masuk Sekarang &rarr;</button>
        <div style="background: var(--bg-subtle); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 14px 16px; font-size: 13px; margin-top: 4px;">
            <strong style="display: block; margin-bottom: 6px; color: var(--ink);">💡 Akun Demo:</strong>
            <div style="color: var(--mut); line-height: 1.6;">
                <div>👤 <strong>Member:</strong> <code>rani.putri@mail.id</code> &middot; <code>pustaka123</code></div>
                <div>⚡ <strong>Admin:</strong> <code>admin@pustaka.id</code> &middot; <code>admin12345</code></div>
            </div>
        </div>
    </form>
</main>
