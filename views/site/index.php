<section class="home">
    <div class="wrapper">
        <h2>Главная</h2>
        <div class="container">
            <p>Добро пожаловать, <?= $user_name ?>!</p>
            <?php if ($role_id == 1): ?>
                <h3>Информация о системе:</h3>
                <p>Всего общежитий: <?= $dormitories_count ?></p>
                <p>Всего комендантов: <?= $commandants_count ?></p>
            <?php endif; ?>

            <?php if ($role_id == 2 && isset($dormitory) && $dormitory): ?>
                <h3>Информация об общежитии:</h3>
                <p>Номер: <?= $dormitory->dormitory_number ?></p>
                <p>Адрес: <?= "г. {$dormitory->city}, ул. {$dormitory->street}, д. {$dormitory->building}" ?></p>
                <p>Цена за год: <?= $dormitory->price ?></p>
                <p>Всего комнат: <?= $rooms_count ?></p>
            <?php elseif ($role_id == 2): ?>
                <p>Вам ещё не назначено общежитие</p>
            <?php endif; ?>  
        </div>
    </div>
</section>