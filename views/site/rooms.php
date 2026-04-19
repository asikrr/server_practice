<section class="rooms">
    <div class="wrapper">
        <h2>Комнаты</h2>
        <div class="controls">
            <form method="get">
                <div class="form-field">
                    <select name="type_filter">
                        <option value="" <?= empty($request->type_filter) ? 'selected' : '' ?>>Все</option>
                        <option value="1" <?= ($request->type_filter ?? '') == '1' ? 'selected' : '' ?>>Мужские</option>
                        <option value="2" <?= ($request->type_filter ?? '') == '2' ? 'selected' : '' ?>>Женские</option>
                        <option value="3" <?= ($request->type_filter ?? '') == '3' ? 'selected' : '' ?>>Семейные</option>
                    </select>
                </div>
                <div class="form-field">
                    <select name="availability_filter">
                        <option value="" <?= empty($request->availability_filter) ? 'selected' : '' ?>>Все</option>
                        <option value="available" <?= ($request->availability_filter ?? '') == 'available' ? 'selected' : '' ?>>Со свободными местами</option>
                    </select>
                </div>
                <button type="submit">Применить</button>
            </form>
        </div>
        <div class="container">
            <div class="table">
                <div class="table-column">
                    <p class="bold-text">Общежитие</p>
                    <?php foreach ($rooms as $r): ?>
                        <p><?= $r->dormitory->dormitory_number ?? '—' ?></p>
                    <?php endforeach; ?>
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
                    <?php foreach ($rooms as $r): ?>
                        <p><?= $r->type->type_name ?? '—' ?></p>
                    <?php endforeach; ?>
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
                    <?php foreach ($rooms ?? [] as $r): ?>
                        <p><?= $r->get_current_residents_count() ?></p>
                    <?php endforeach; ?>
                </div>
                <?php if (!$is_admin): ?> 
                <div class="table-column">
                    <p class="bold-text">Заселение</p>
                    <?php foreach ($rooms ?? [] as $r): ?>
                        <?php if ($r->get_current_residents_count() < $r->capacity): ?>
                            <a href="<?= app()->route->getUrl('/resident_create/' . $r->room_id) ?>" class="underline-text">Заселить</a>
                        <?php else: ?>
                            <p><i>Заполнена</i></p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <div class="table-column">
                    <p class="bold-text">Редактирование</p>
                    <?php foreach ($rooms ?? [] as $r): ?>
                        <?php if ($r->get_current_residents_count() == 0): ?>
                            <a href="<?= app()->route->getUrl('/room_update/' . $r->room_id) ?>" class="underline-text">Редактировать</a>
                        <?php else: ?>
                            <p>-</p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Удаление</p>
                    <?php foreach ($rooms ?? [] as $r): ?>
                        <?php if ($r->get_current_residents_count() == 0): ?>
                            <form method="POST" action="<?= app()->route->getUrl('/room_delete/' . $r->room_id) ?>">
                                <input type="hidden" name="csrf_token" value="<?= app()->auth::generateCSRF() ?>">
                                <button class="underline-text danger-text">Удалить</button>
                            </form>
                        <?php else: ?>
                            <p>-</p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>