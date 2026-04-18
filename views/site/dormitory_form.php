<section class="dormitory-form">
    <div class="wrapper">
        <h3><?= $message ?? ''; ?></h3>
        <h2><?= $page_title ?></h2>
        <div class="container">
            <form method="post" class="form">
                <input name="csrf_token" type="hidden" value="<?= app()->auth::generateCSRF() ?>"/>
                <div class="form-fields">
                    <div class="form-field">
                        <label for="dorm-num">Номер</label>
                        <input type="text" name="dormitory_number" id="dorm-num" value="<?= $dormitory->dormitory_number ?? '' ?>">
                    </div>
                    <div class="form-field">
                        <label for="city">Город</label>
                        <input type="text" name="city" id="city" value="<?= $dormitory->city ?? '' ?>">
                    </div>
                    <div class="form-field">
                        <label for="street">Улица</label>
                        <input type="text" name="street" id="street" value="<?= $dormitory->street ?? '' ?>">
                    </div>
                </div>
                <div class="form-fields">
                    <div class="form-field">
                        <label for="building">Здание</label>
                        <input type="text" name="building" id="building" value="<?= $dormitory->building ?? '' ?>">
                    </div>
                    <div class="form-field">
                        <label for="price">Цена</label>
                        <input type="number" name="price" id="price" value="<?= $dormitory->price ?? '' ?>">
                    </div>
                </div>
                <button>Сохранить</button>
            </form>
        </div>
    </div>
</section>