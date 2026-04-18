<section class="commandants">
    <div class="wrapper">
        <div class="container-header">
            <h2>Коменданты</h2>
            <a href="<?= app()->route->getUrl('/commandant_create') ?>" class="underline-text">Добавить</a>
        </div>
        <div class="container">
            <div class="table">
                <div class="table-column">
                    <p class="bold-text">ФИО</p>
                    <?php
                    foreach ($commandants ?? [] as $c) {
                        echo '<p>' . $c->full_name . '</p>';
                    }
                    ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Общежитие</p>
                    <?php
                    foreach ($commandants ?? [] as $c) {
                        $d = $dormitories->where('user_id', $c->user_id)->first();
                        echo '<p>' . ($d ? '№' . $d->dormitory_number : 'Не назначено') . '</p>';
                    }
                    ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Логин</p>
                    <?php
                    foreach ($commandants ?? [] as $c) {
                        echo '<p>' . $c->login . '</p>';
                    }
                    ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Редактирование</p>
                    <?php foreach ($commandants ?? [] as $c): ?>
                        <a href="<?= app()->route->getUrl('/commandant_update/' . $c->user_id) ?>" class="underline-text">Редактировать</a>
                    <?php endforeach; ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Удаление</p>
                    <?php foreach ($commandants ?? [] as $c): ?>
                        <?php if (!in_array($c->user_id, $busyIds)): ?>
                            <form method="POST" action="<?= app()->route->getUrl('/commandant_delete/' . $c->user_id) ?>">
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