<?php use Src\Session; ?>

<section class="home">
    <div class="wrapper">
        <h2>Главная</h2>
        <div class="container">
            <p>Добро пожаловать, <?= app()->auth::user()->full_name ?>!</p>
            <?php if (Session::get('role_id') == 1): ?>
                <h3>Информация о системе:</h3>
                <p>Всего общежитий:</p>
                <p>Всего комендантов:</p>
            <?php endif; ?>

            <?php if (Session::get('role_id') == 2): ?>
                <h3>Информация об общежитии:</h3>
                <p>Номер:</p>
                <p>Адрес:</p>
                <p>Цена за год:</p>
                <p>Всего комнат:</p>
                <p>Всего мест:</p>
                <p>Свободных мест:</p>
            <?php endif; ?>  
        </div>
    </div>
</section>