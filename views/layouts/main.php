<?php 
$paths = require __DIR__ . '/../../config/path.php';
use Src\Session;
 ?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
            content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Управление общежитием</title>
    <link rel="stylesheet" href="/<?= $paths['root'] ?>/public/css/main.css">
</head>
<body>
<header>
    <div class="wrapper">
        <h1><a href="<?= app()->route->getUrl('/') ?>">Управление общежитием</a></h1>
        <nav>
            <?php if (!app()->auth::check()): ?>
                <a href="<?= app()->route->getUrl('/login') ?>">Вход</a>
            <?php else: ?>
                <a href="<?= app()->route->getUrl('/') ?>">Главная</a>
                <?php if (Session::get('role_id') == 1): ?>
                    <a href="<?= app()->route->getUrl('/dormitories') ?>">Общежития</a>
                    <a href="<?= app()->route->getUrl('/commandants') ?>">Коменданты</a>
                <?php endif; ?>

                <?php if (Session::get('role_id') == 2): ?>
                    <a href="<?= app()->route->getUrl('/residents') ?>">Жильцы</a>
                    <a href="<?= app()->route->getUrl('/debtors') ?>">Должники</a>
                <?php endif; ?>

                <a href="<?= app()->route->getUrl('/rooms') ?>">Комнаты</a>
                <a href="<?= app()->route->getUrl('/logout') ?>" class="danger-text">Выйти</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main>
    <?= $content ?? '' ?>
</main>

</body>
</html>