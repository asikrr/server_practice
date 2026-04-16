<section class="home">
    <div class="wrapper">
        <h2>Главная</h2>
        <div class="container">
            <p>Добро пожаловать, <?= app()->auth::user()->full_name ?>!</p>
            <h3>Информация об общежитии:</h3>
            <p>Номер:</p>
            <p>Цена за год:</p>
            <p>Всего комнат:</p>
            <p>Свободных мест:</p>
        </div>
    </div>
</section>