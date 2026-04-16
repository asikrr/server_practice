<section class="commandants">
    <div class="wrapper">
        <div class="container-header">
            <h2>Коменданты</h2>
            <a href="<?= app()->route->getUrl('/commandant_form') ?>" class="underline-text">Добавить</a>
        </div>
        <div class="container">
            <div class="table">
                <div class="table-column">
                    <p class="bold-text">ФИО</p>
                    <?php
                    foreach ($commandants as $c) {
                        echo '<p>' . $c->full_name . '</p>';
                    }
                    ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Общежитие</p>
                </div>
                <div class="table-column">
                    <p class="bold-text">Логин</p>
                    <?php
                    foreach ($commandants as $c) {
                        echo '<p>' . $c->login . '</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>