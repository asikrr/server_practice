<section class="room-form">
    <div class="wrapper">
        <h3><?= $message ?? ''; ?></h3>
        <h2><?= $page_title ?></h2>
        <div class="container">
            <form method="post" class="form">
                <input name="csrf_token" type="hidden" value="<?= app()->auth::generateCSRF() ?>"/>
                <div class="form-fields">
                    <div class="form-field">
                        <label for="room-number">Номер</label>
                        <input type="text" name="room_number" id="room-number" value="<?= $room->room_number ?? '' ?>">
                    </div>
                    <div class="form-field">
                        <label for="floor">Этаж</label>
                        <input type="text" name="floor" id="floor" value="<?= $room->floor ?? '' ?>">
                    </div>
                    <div class="form-field">
                        <label for="capacity">Вместимость</label>
                        <input type="number" name="capacity" id="capacity" value="<?= $room->capacity ?? '' ?>">
                    </div>
                </div>
                <div class="form-fields">
                    <div class="form-field">
                        <label for="room-type">Тип</label>
                        <select name="type_id" id="room-type">
                            <option value="">Выберите тип</option>
                            <?php foreach ($types as $type): ?>
                                <option value="<?= $type->type_id ?>" <?= ($room && $type->type_id == $room->type_id) ? 'selected' : '' ?>>
                                    <?= $type->type_name ?>
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