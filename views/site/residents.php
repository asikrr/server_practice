<section class="residents">
    <div class="wrapper">
        <h2>Жильцы</h2>
        <div class="controls">
            <form method="get">
                <div class="form-field">
                    <select name="residents-sort">
                        <option value="alphabet_asc">По алфавиту (А-Я)</option>
                        <option value="alphabet_desc">По алфавиту (Я-А)</option>
                    </select>
                </div>
                <button>Применить</button>
            </form>
            <form method="get">
                <div class="form-field">
                    <input type="text" 
                        name="search" 
                        value="<?= $search ?? ''?>" 
                        placeholder="Поиск по ФИО">
                    <button type="submit">Найти</button>
                </div>
            </form>
        </div>
        <div class="container">
            <div class="table">
                <div class="table-column">
                    <p class="bold-text">ФИО</p>
                    <?php foreach ($residents ?? [] as $r): ?>
                        <p><?= "{$r->last_name} {$r->first_name} {$r->patronymic}" ?></p>
                    <?php endforeach; ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Паспорт</p>
                    <?php foreach ($residents ?? [] as $r): ?>
                        <p><?= $r->passport ?></p>
                    <?php endforeach; ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Пол</p>
                    <?php foreach ($residents ?? [] as $r): ?>
                        <p><?= $r->gender->gender_name ?></p>
                    <?php endforeach; ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Статус</p>
                    <?php foreach ($residents ?? [] as $r): ?>
                        <p><?= $r->status->status_name ?></p>
                    <?php endforeach; ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Комната</p>
                    <?php foreach ($residents ?? [] as $r): ?>
                        <?php $cur = $r->get_current_residence(); ?>
                        <p>
                            <?= $cur ? $cur->room->room_number ?? '?' : '—' ?>
                        </p>
                    <?php endforeach; ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Приказ</p>
                    <?php foreach ($residents ?? [] as $r): ?>
                        <?php $cur = $r->get_current_residence(); ?>
                        <p><?= $cur ? $cur->residence_order_num : '—' ?></p>
                    <?php endforeach; ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Проживание</p>
                    <?php foreach ($residents ?? [] as $r): ?>
                        <?php $cur = $r->get_current_residence(); ?>
                        <p><?= $cur ? $cur->date_of_entry . ' - ' . $cur->date_of_departure : '—' ?></p>
                    <?php endforeach; ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Квитанция</p>
                    <?php foreach ($residents ?? [] as $r): ?>
                        <?php $cur = $r->get_current_residence(); ?>
                        <p>
                            <?php if ($cur && $cur->payment && $cur->payment->receipt_file): ?>
                                <a href="<?= $cur->payment->receipt_file ?>" target="_blank" class="underline-text">Скачать</a>
                            <?php else: ?>
                                Нет
                            <?php endif; ?>
                        </p>
                    <?php endforeach; ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Редактирование</p>
                    <?php foreach ($residents ?? [] as $r): ?>
                        <a href="<?= app()->route->getUrl('/resident_update/' . $r->resident_id) ?>" class="underline-text">Редактировать</a>
                    <?php endforeach; ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Выселение</p>
                    <?php foreach ($residents as $r): ?>
                        <?php if ($r->get_current_residence()): ?>
                            <form method="POST" 
                                action="<?= app()->route->getUrl('/resident_checkout/' . $r->resident_id) ?>">
                                <input type="hidden" name="csrf_token" value="<?= app()->auth::generateCSRF() ?>">
                                <button type="submit" class="underline-text danger-text">Выселить</button>
                            </form>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>