<?php
$setupError = isset($error) && is_string($error) ? $error : null;
$canSetup = isset($setupEnabled) && $setupEnabled === true;
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengaturan Awal - East Regional FAT</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; background: #eef3f8; color: #172b4d; font: 16px Arial, sans-serif; }
        main { width: min(100%, 460px); padding: 32px; border-radius: 16px; background: #fff; box-shadow: 0 12px 36px #172b4d1a; }
        h1 { margin: 0 0 8px; font-size: 25px; }
        p { color: #52627a; line-height: 1.5; }
        label { display: block; margin: 18px 0 6px; font-weight: 700; }
        input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font: inherit; }
        button { width: 100%; margin-top: 22px; padding: 12px; border: 0; border-radius: 8px; background: #1769aa; color: #fff; font: inherit; font-weight: 700; cursor: pointer; }
        .error { padding: 12px; border-radius: 8px; background: #fff0f0; color: #a52626; }
    </style>
</head>
<body>
<main>
    <h1>Pengaturan administrator awal</h1>
    <?php if ($setupError !== null): ?><p class="error" role="alert"><?= esc($setupError) ?></p><?php endif ?>
    <?php if ($canSetup): ?>
        <p>Buat akun admin pertama. Kunci pengaturan hanya dipakai satu kali dan jangan bagikan kepada siapa pun.</p>
        <form method="post" action="<?= site_url('setup') ?>">
            <?= csrf_field() ?>
            <label for="setup_key">Kunci pengaturan</label>
            <input id="setup_key" name="setup_key" type="password" autocomplete="off" required>
            <label for="name">Nama</label>
            <input id="name" name="name" maxlength="100" autocomplete="name" required>
            <label for="username">Username admin</label>
            <input id="username" name="username" maxlength="50" autocomplete="username" required>
            <label for="password">Password admin</label>
            <input id="password" name="password" type="password" minlength="12" maxlength="255" autocomplete="new-password" required>
            <button type="submit">Buat administrator</button>
        </form>
    <?php else: ?>
        <p>Pengaturan awal tidak tersedia. Jika ini pemasangan pertama, tambahkan kunci acak minimal 32 karakter ke file `.env` di folder website, lalu buka halaman ini kembali.</p>
        <p><a href="<?= site_url('login') ?>">Kembali ke halaman login</a></p>
    <?php endif ?>
</main>
</body>
</html>
