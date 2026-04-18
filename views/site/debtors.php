<section class="debtors">
    <div class="wrapper">
        <h2>Должники</h2>
        <div class="container">
            <div class="table">
                <div class="table-column">
                    <p class="bold-text">ФИО</p>
                    <?php foreach ($debtors as $d): ?>
                        <p><?= $d['resident']->last_name ?> <?= $d['resident']->first_name ?> <?= $d['resident']->patronymic ?></p>
                    <?php endforeach; ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Комната</p>
                    <?php foreach ($debtors as $d): ?>
                        <p><?= $d['room_number'] ?></p>
                    <?php endforeach; ?>
                </div>
                <div class="table-column">
                    <p class="bold-text">Размер долга</p>
                    <?php foreach ($debtors as $d): ?>
                        <p><?= $d['debt'] ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>