<section class="rooms">
    <div class="wrapper">
        <h2>Комнаты</h2>
        <div class="controls">
            <form method="get">
                <div class="form-field">
                    <select name="type_filter">
                        <option value="">Все</option>
                        <option value="family">Семейные</option>
                        <option value="male">Мужские</option>
                        <option value="female">Женские</option>
                    </select>
                </div>
                <div class="form-field">
                    <select name="availability_filter">
                        <option value="">Все</option>
                        <option value="available">Со свободными местами</option>
                    </select>
                </div>
                <button>Применить</button>
            </form>
        </div>
        <div class="container">
            <div class="table">
                <div class="table-column">
                    <p class="bold-text">Общежитие</p>
                    <?php
                    foreach ($rooms ?? [] as $r) {
                        $dormitory = $dormitories->where('dormitory_id', $r->dormitory_id)->first();
                        echo '<p>' . $dormitory->dormitory_number . '</p>';
                    }
                    ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Номер</p>
                    <?php
                    foreach ($rooms ?? [] as $r) {
                        echo '<p>' . $r->room_number . '</p>';
                    }
                    ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Этаж</p>
                    <?php
                    foreach ($rooms ?? [] as $r) {
                        echo '<p>' . $r->floor . '</p>';
                    }
                    ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Тип</p>
                    <?php
                    foreach ($rooms ?? [] as $r) {
                        $type = $types->where('type_id', $r->type_id)->first();
                        echo '<p>' . $type->type_name . '</p>';
                    }
                    ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Вместимость</p>
                    <?php
                    foreach ($rooms ?? [] as $r) {
                        echo '<p>' . $r->capacity . '</p>';
                    }
                    ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Количество жильцов</p>
                    <?php foreach ($rooms ?? [] as $room): ?>
                        <p><?= $room->residences()->whereNull('actual_date_of_departure')->count() ?></p>
                    <?php endforeach; ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Заселение</p>
                    <?php foreach ($rooms ?? [] as $room): ?>
                        <a href="<?= app()->route->getUrl('/resident_create/' . $room->room_id) ?>" class="underline-text">Заселить</a>
                    <?php endforeach; ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Редактирование</p>
                    <?php foreach ($rooms ?? [] as $r): ?>
                        <a href="<?= app()->route->getUrl('/room_update/' . $r->room_id) ?>" class="underline-text">Редактировать</a>
                    <?php endforeach; ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Удаление</p>
                    <?php foreach ($rooms ?? [] as $r): ?>
                        <form method="POST" action="<?= app()->route->getUrl('/room_delete/' . $r->room_id) ?>">
                            <input type="hidden" name="csrf_token" value="<?= app()->auth::generateCSRF() ?>">
                            <button class="underline-text danger-text">Удалить</button>
                        </form>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>