<section class="resident-form">
    <div class="wrapper">
        <h3><?= $message ?? ''; ?></h3>
        <h2><?= $page_title ?? 'Добавление жильца' ?></h2>
        <div class="container">
            <form method="post" enctype="multipart/form-data" class="form">
                <input type="hidden" name="csrf_token" value="<?= app()->auth::generateCSRF() ?>">
                <?php if (isset($room_id)): ?>
                    <input type="hidden" name="room_id" value="<?= $room_id ?>">
                <?php endif; ?>
                <div class="form-fields">
                    <div class="form-field">
                        <label for="last-name">Фамилия</label>
                        <input type="text" id="last-name" name="last_name" value="<?= $resident->last_name ?? '' ?>">
                    </div>
                    <div class="form-field">
                        <label for="first-name">Имя</label>
                        <input type="text" id="first-name" name="first_name" value="<?= $resident->first_name ?? '' ?>">
                    </div>
                    <div class="form-field">
                        <label for="patronymic">Отчество</label>
                        <input type="text" id="patronymic" name="patronymic" value="<?= $resident->patronymic ?? '' ?>">
                    </div>
                </div>
                <div class="form-fields">
                    <div class="form-field">
                        <label for="passport">Паспорт</label>
                        <input type="text" id="passport" name="passport" value="<?= $resident->passport ?? '' ?>">
                    </div>
                    <div class="form-field">
                        <label for="gender">Пол</label>
                        <select id="gender" name="gender_id" <?= $resident ? 'disabled' : '' ?>>
                            <option value="">Выберите</option>
                            <?php foreach ($genders ?? [] as $g): ?>
                                <option value="<?= $g->gender_id ?>" <?= $resident && $g->gender_id == $resident->gender_id ? 'selected' : '' ?>>
                                    <?= $g->gender_name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="status">Статус</label>
                        <select id="status" name="status_id">
                            <option value="">Выберите</option>
                            <?php foreach ($statuses ?? [] as $s): ?>
                                <option value="<?= $s->status_id ?>" <?= $resident && $s->status_id == $resident->status_id ? 'selected' : '' ?>>
                                    <?= $s->status_name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-fields">
                    <div class="form-field">
                        <label for="residence-order-num">№ приказа о заселении</label>
                        <input type="text" id="residence-order-num" name="residence_order_num" value="<?= $residence->residence_order_num ?? '' ?>">
                    </div>
                    <div class="form-field">
                        <label for="date-of-entry">Дата заезда</label>
                        <input type="date" 
                            id="date-of-entry"
                            name="date_of_entry" 
                            value="<?= $residence->date_of_entry ?? date('Y-m-d') ?>" 
                            <?= $residence ? 'disabled' : '' ?> 
                            required>
                    </div>
                    <div class="form-field">
                        <label for="date-of-departure">Дата выезда</label>
                        <input type="date" id="date-of-departure" name="date_of_departure" value="<?= $residence->date_of_departure ?? '' ?>">
                    </div>
                    <div class="form-field">
                        <label for="receipt-file">Квитанция об оплате</label>
                        <input type="file" id="receipt-file" name="receipt_file" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>
                <button type="submit">Сохранить</button>
            </form>
        </div>
    </div>
</section>