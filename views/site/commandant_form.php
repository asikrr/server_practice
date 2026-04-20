<section class="commandant-form">
    <div class="wrapper">
        <h3><?= $message ?? ''; ?></h3>
        <h2><?= $page_title ?></h2>
        <div class="container">
            <form method="post" class="form">
                <input name="csrf_token" type="hidden" value="<?= app()->auth::generateCSRF() ?>"/>
                <?php if (!$commandant): ?>
                <div class="form-fields">
                    <div class="form-field">
                        <label for="full_name">ФИО</label>
                        <input type="text" name="full_name" id="full-name" value="">
                    </div>
                    <div class="form-field">
                        <label for="login">Логин</label>
                        <input type="text" name="login" id="login" value="">
                    </div>
                    <div class="form-field">
                        <label for="password">Пароль</label>
                        <input type="password" name="password" id="password">
                    </div>
                </div>
                <?php endif; ?>
                <div class="form-fields">
                    <div class="form-field">
                        <label for="dorm-select">Прикрепить к общежитию</label>
                        <select name="dormitory_id" id="dorm-select">
                            <option value="">Нет</option>
                            <?php foreach ($free_dorms as $d): ?>
                                <option value="<?= $d->dormitory_id ?>"
                                    <?= ($commandant && $d->user_id == $commandant->user_id) ? 'selected' : '' ?>>
                                    Общежитие №<?= $d->dormitory_number ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button>Сохранить</button>
            </form>
        </div>
    </div>
</section>