<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pustaka.ku – Katalog &amp; Sewa E-Book</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="nav">
    <a class="logo" href="index.php"><span class="lm">P</span><span>Pustaka<span class="accent">.ku</span></span></a>
    <?php if (!empty($member)): ?>
    <nav class="menu" aria-label="Navigasi utama">
        <a class="nl<?= ($page ?? '') === 'dashboard' ? ' on' : '' ?>" href="index.php?page=dashboard">Dashboard</a>
        <a class="nl<?= ($page ?? '') === 'katalog' ? ' on' : '' ?>" href="index.php?page=katalog">Katalog</a>
    </nav>
    <div class="user"><span>👋 <?= e($member->getNama()) ?></span><a class="btn o" href="index.php?page=logout">Keluar</a></div>
    <?php elseif (!empty($admin)): ?>
    <nav class="menu" aria-label="Navigasi admin">
        <a class="nl<?= ($page ?? '') === 'admin' ? ' on' : '' ?>" href="index.php?page=admin">Dashboard Admin</a>
        <a class="nl<?= ($page ?? '') === 'admin-buku' ? ' on' : '' ?>" href="index.php?page=admin-buku">Kelola Buku</a>
    </nav>
    <div class="user"><span>⚡ <?= e($admin->getNama()) ?> <span class="badge">Admin</span></span><a class="btn o" href="index.php?page=logout">Keluar</a></div>
    <?php endif; ?>
</header>
<?php if (!empty($_SESSION['flash'])): $f = $_SESSION['flash']; unset($_SESSION['flash']); ?>
    <div class="flash <?= e($f['type']) ?>" role="status"><span><?= e($f['msg']) ?></span><button type="button" class="x" aria-label="Tutup pesan">✕</button></div>
<?php endif; ?>
<?= $content ?? '' ?>
<footer class="foot">
    <p>&copy; <?= date('Y') ?> <strong>Pustaka<span class="accent">.ku</span></strong> &middot; Platform pinjam e-book kekinian untuk mahasiswa &amp; pegiat web</p>
</footer>
<script src="assets/js/app.js"></script>
</body>
</html>
