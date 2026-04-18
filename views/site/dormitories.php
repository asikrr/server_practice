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
                    <?php
                    foreach ($dormitories ?? [] as $d) {
                        $commandant = $commandants->where('user_id', $d->user_id)->first();
                        echo '<p>' . ($commandant ? $commandant->full_name : 'Не назначен') . '</p>';
                    }
                    ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Кол-во комнат</p>
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
                    <p class="bold-text">Комнаты</p>
                    <?php foreach ($dormitories ?? [] as $d): ?>
                        <a href="<?= app()->route->getUrl('/room_create/' . $d->dormitory_id) ?>" class="underline-text">Добавить комнату</a>
                    <?php endforeach; ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Редактирование</p>
                    <?php foreach ($dormitories ?? [] as $d): ?>
                        <a href="<?= app()->route->getUrl('/dormitory_update/' . $d->dormitory_id) ?>" class="underline-text">Редактировать</a>
                    <?php endforeach; ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Удаление</p>
                    <?php foreach ($dormitories ?? [] as $d): ?>
                        <?php if (!in_array($d->dormitory_id, $busyIds)): ?>
                            <form method="POST" action="<?= app()->route->getUrl('/dormitory_delete/' . $d->dormitory_id) ?>">
                                <input type="hidden" name="csrf_token" value="<?= app()->auth::generateCSRF() ?>">
                                <button class="underline-text danger-text">Удалить</button>
                            </form>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>