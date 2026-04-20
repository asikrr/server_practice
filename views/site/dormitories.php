<section class="dormitories">
    <div class="wrapper">
        <div class="container-header">
            <h2>Общежития</h2>
            <a href="<?= app()->route->getUrl('/dormitory_create') ?>" class="underline-text">Добавить</a>
        </div>
        <div class="container">
            <div class="table">
                <div class="table-column">
                    <p class="bold-text">№</p>
                    <?php
                    foreach ($dormitories ?? [] as $d) {
                        echo '<p>' . $d->dormitory_number . '</p>';
                    }
                    ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Адрес</p>
                    <?php
                    foreach ($dormitories ?? [] as $d) {
                        echo '<p>' . 'г. ' . $d->city . ', ул. ' . $d->street . ' ' . $d->building . '</p>';
                    }
                    ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Комендант</p>
                    <?php foreach ($dormitories ?? [] as $d): ?>
                        <p><?= $d->commandant->full_name ?? 'Не назначен' ?></p>
                    <?php endforeach; ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Кол-во комнат</p>
                    <?php
                    foreach ($dormitories ?? [] as $d) {
                        echo '<p>' . $d->get_rooms_count() . '</p>';
                    }
                    ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Цена</p>
                    <?php
                    foreach ($dormitories ?? [] as $d) {
                        echo '<p>' . $d->price . '</p>';
                    }
                    ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Добавление комнаты</p>
                    <?php foreach ($dormitories ?? [] as $d): ?>
                        <a href="<?= app()->route->getUrl('/room_create/' . $d->dormitory_id) ?>" class="underline-text">Добавить комнату</a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>