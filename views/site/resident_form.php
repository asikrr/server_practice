<section class="resident-form">
    <div class="wrapper">
        <h2>Добавление жильца</h2>
        <div class="container">
            <form method="post" class="form">
                <div class="form-fields">
                    <div class="form-field">
                        <label for="last-name">Фамилия</label>
                        <input type="text" name="last-name" id="last-name">
                    </div>
                    <div class="form-field">
                        <label for="first-name">Имя</label>
                        <input type="text" name="first-name" id="first-name">
                    </div>
                    <div class="form-field">
                        <label for="patronymic">Отчество</label>
                        <input type="text" name="patronymic" id="patronymic">
                    </div>
                </div>
                <div class="form-fields">
                    <div class="form-field">
                        <label for="passport">Паспорт</label>
                        <input type="text" name="passport" id="passport">
                    </div>
                    <div class="form-field">
                        <label for="gender-select">Пол</label>
                        <select id="gender-select">
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="residence-order-num">№ приказа о заселении</label>
                        <input type="text" name="residence-order-num" id="residence-order-num">
                    </div>
                </div>
                <button>Сохранить</button>
            </form>
        </div>
    </div>
</section>