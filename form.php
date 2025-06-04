<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Задание 4</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css" />
    <style>
        .error {
            color: red;
            font-weight: bold;
        }
        .error-field {
            border: 2px solid red;
        }
        .credentials {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid var(--success-color);
        }
    </style>
</head>

<body>
    <header>
        <div class="container">
            <h1 class="welcome">Форма подачи заявки</h1>
            <?php if ($logged_in): ?>
                <p>Вы вошли как <?php echo htmlspecialchars($_SESSION['login']); ?></p>
                <a href="?logout=1" class="btn btn-danger">Выйти</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="container">
        <?php if (!empty($_COOKIE['generated_login']) && !empty($_COOKIE['generated_password'])): ?>
            <div class="credentials">
                <h4>Ваши учетные данные для входа:</h4>
                <p><strong>Логин:</strong> <?php echo htmlspecialchars($_COOKIE['generated_login']); ?></p>
                <p><strong>Пароль:</strong> <?php echo htmlspecialchars($_COOKIE['generated_password']); ?></p>
                <p class="text-muted">Сохраните эти данные, они больше не будут показаны.</p>
            </div>
        <?php endif; ?>

        <?php if (!$logged_in): ?>
            <div class="change_forma block_forma mb-4">
                <h3 class="fname">Вход в систему</h3>
                <?php if (isset($login_error)): ?>
                    <div class="alert alert-danger"><?php echo $login_error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="login" value="1">
                    <div class="form-group">
                        <label for="login">Логин:</label>
                        <input type="text" class="form-control" id="login" name="login" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Пароль:</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Войти</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="change_forma block_forma">
            <div class="fname"><h3>HTML форма</h3></div>
            
            <?php if (!empty($messages)): ?>
                <div class="messages">
                    <?php foreach ($messages as $message): ?>
                        <?php echo $message; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form action="index.php" method="POST">
                <div class="otsyp">
                    <label>ФИО: 
                        <input type="text" name="fio" class="<?php echo !empty($errors['fio']) ? 'error-field' : ''; ?>" 
                               value="<?php echo setValue('fio_value'); ?>" required pattern="[A-Za-zА-Яа-яЁё\s]+" maxlength="150">
                    </label><br>

                    <label>Телефон: 
                        <input type="tel" name="phone" class="<?php echo !empty($errors['phone']) ? 'error-field' : ''; ?>" 
                               value="<?php echo setValue('phone_value'); ?>" required pattern="\+?[0-9]{11,15}">
                    </label><br>

                    <label>Email: 
                        <input type="email" name="email" class="<?php echo !empty($errors['email']) ? 'error-field' : ''; ?>" 
                               value="<?php echo setValue('email_value'); ?>" required>
                    </label><br>

                    <label>Дата рождения: 
                        <input type="date" name="birth_date" class="<?php echo !empty($errors['birth_date']) ? 'error-field' : ''; ?>" 
                               value="<?php echo setValue('birth_date_value'); ?>" required>
                    </label><br>

                    <p>Пол:</p>
                    <input type="radio" id="male" name="gender" value="male" <?php echo setChecked('gender_value', 'male'); ?> required>
                    <label for="male">Мужской</label><br>
                    <input type="radio" id="female" name="gender" value="female" <?php echo setChecked('gender_value', 'female'); ?>>
                    <label for="female">Женский</label><br><br>

                    <label for="language">Любимый язык программирования:</label>
                    <br>
                    <select id="language" name="languages[]" multiple required class="<?php echo !empty($errors['languages']) ? 'error-field' : ''; ?>">
                        <option value="1" <?php echo setSelected('languages_value', '1'); ?>>Pascal</option>
                        <option value="2" <?php echo setSelected('languages_value', '2'); ?>>C</option>
                        <option value="3" <?php echo setSelected('languages_value', '3'); ?>>C++</option>
                        <option value="4" <?php echo setSelected('languages_value', '4'); ?>>JavaScript</option>
                        <option value="5" <?php echo setSelected('languages_value', '5'); ?>>PHP</option>
                        <option value="6" <?php echo setSelected('languages_value', '6'); ?>>Python</option>
                        <option value="7" <?php echo setSelected('languages_value', '7'); ?>>Java</option>
                        <option value="8" <?php echo setSelected('languages_value', '8'); ?>>Haskell</option>
                        <option value="9" <?php echo setSelected('languages_value', '9'); ?>>Clojure</option>
                        <option value="10" <?php echo setSelected('languages_value', '10'); ?>>Prolog</option>
                        <option value="11" <?php echo setSelected('languages_value', '11'); ?>>Scala</option>
                        <option value="12" <?php echo setSelected('languages_value', '12'); ?>>Go</option>
                    </select>
                    <br><br />

                    <label>Биография:</label><br>
                    <textarea name="bio" rows="5" required class="<?php echo !empty($errors['bio']) ? 'error-field' : ''; ?>"><?php echo setValue('bio_value'); ?></textarea><br>
                    
                    <label><input type="checkbox" name="agreement" <?php echo setValue('agreement_value') ? 'checked' : ''; ?> required> С условиями контракта ознакомлен</label><br>

                    <input type="submit" value="Сохранить">
                </div>
            </form>
        </div>
    </div>
</body>

</html>