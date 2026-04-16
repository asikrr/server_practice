<section class="auth">
    <div class="wrapper">
        <h3><?= $message ?? ''; ?></h3>
        <h3><?= app()->auth->user()->name ?? ''; ?></h3>
        
        <h2>Вход</h2>
        <?php
        if (!app()->auth::check()):
            ?>
        <div class="container">
            <form method="post" class="auth-form">
                <div class="form-field">
                    <label for="login">Логин</label>
                    <input type="text" name="login" id="login">
                </div>
                <div class="form-field">
                    <label for="password">Пароль</label>
                    <input type="password" name="password" id="password">
                </div>
                <button>Войти</button>
            </form>
        </div>
    </div>
</section>
<?php endif;