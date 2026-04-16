<section class="commandant-form">
    <div class="wrapper">
        <h3><?= $message ?? ''; ?></h3>
        <h2>Добавление коменданта</h2>
        <div class="container">
            <form method="post" class="form">
                <input name="csrf_token" type="hidden" value="<?= app()->auth::generateCSRF() ?>"/>
                <div class="form-fields">
                    <div class="form-field">
                        <label for="full_name">ФИО</label>
                        <input type="text" name="full_name" id="full-name">
                    </div>
                    <div class="form-field">
                        <label for="login">Логин</label>
                        <input type="text" name="login" id="login">
                    </div>
                    <div class="form-field">
                        <label for="password">Пароль</label>
                        <input type="password" name="password" id="password">
                    </div>
                </div>
                <div class="form-fields">
                    <div class="form-field">
                        <label for="dorm-select">Прикрепить к общежитию</label>
                        <select id="dorm-select">
                        </select>
                    </div>
                </div>
                <button>Сохранить</button>
            </form>
        </div>
    </div>
</section>