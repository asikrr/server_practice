<section class="room-form">
    <div class="wrapper">
        <h2>Добавление комнаты</h2>
        <div class="container">
            <form method="post" class="form">
                <div class="form-fields">
                    <div class="form-field">
                        <label for="room-number">Номер</label>
                        <input type="text" name="room-number" id="room-number">
                    </div>
                    <div class="form-field">
                        <label for="floor">Этаж</label>
                        <input type="text" name="floor" id="floor">
                    </div>
                    <div class="form-field">
                        <label for="capacity">Вместимость</label>
                        <input type="number" name="capacity" id="capacity">
                    </div>
                </div>
                <div class="form-fields">
                    <div class="form-field">
                        <label for="dorm-select">Общежитие</label>
                        <select id="dorm-select">
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="room-type">Тип</label>
                        <select id="room-type">
                        </select>
                    </div>
                </div>
                <button>Сохранить</button>
            </form>
        </div>
    </div>
</section>