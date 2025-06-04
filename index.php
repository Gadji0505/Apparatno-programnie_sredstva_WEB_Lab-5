<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

function setValue($field) {
    return isset($_COOKIE[$field]) ? htmlspecialchars($_COOKIE[$field]) : '';
}

function setChecked($field, $value) {
    return (isset($_COOKIE[$field]) && $_COOKIE[$field] == $value) ? 'checked' : '';
}

function setSelected($field, $value) {
    return (isset($_COOKIE[$field]) && in_array($value, (array)json_decode($_COOKIE[$field], true)) ? 'selected' : '';
}

// Handle login
if (isset($_POST['login_action'])) {
    try {
        $db = new PDO('mysql:host=localhost;dbname=u68653', 'u68653', '7251537', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $stmt = $db->prepare("SELECT id, password_hash FROM applications WHERE login = ?");
        $stmt->execute([$_POST['login']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($_POST['password'], $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: index.php');
            exit();
        } else {
            $messages[] = '<div class="error">Неверный логин или пароль</div>';
        }
    } catch (PDOException $e) {
        $messages[] = '<div class="error">Ошибка базы данных</div>';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

// Show login form if not authenticated
if (!isset($_SESSION['user_id']) && !isset($_POST['login_action'])) {
    include('login_form.php');
    exit();
}

// Handle form submission for editing data
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $messages = array();
    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', time() - 3600);
        $messages[] = 'Данные сохранены!';
    }
    
    if (!empty($_COOKIE['credentials'])) {
        $credentials = json_decode($_COOKIE['credentials'], true);
        $messages[] = '<div class="success">Ваши учетные данные для входа:<br>Логин: ' . 
                      htmlspecialchars($credentials['login']) . '<br>Пароль: ' . 
                      htmlspecialchars($credentials['password']) . '</div>';
        setcookie('credentials', '', time() - 3600);
    }
    
    $errors = array();
    $errors['fio'] = !empty($_COOKIE['fio_error']);
    $errors['phone'] = !empty($_COOKIE['phone_error']);
    $errors['email'] = !empty($_COOKIE['email_error']);
    $errors['birth_date'] = !empty($_COOKIE['birth_date_error']);
    $errors['gender'] = !empty($_COOKIE['gender_error']);
    $errors['languages'] = !empty($_COOKIE['languages_error']);
    $errors['bio'] = !empty($_COOKIE['bio_error']);
    $errors['agreement'] = !empty($_COOKIE['agreement_error']);
    
    if ($errors['fio']) {
        setcookie('fio_error', '', time() - 3600);
        $messages[] = '<div class="error">Некорректное ФИО. Допустимы только буквы и пробелы.</div>';
    }
    if ($errors['phone']) {
        setcookie('phone_error', '', time() - 3600);
        $messages[] = '<div class="error">Некорректный номер телефона. Допустимый формат: +71234567890 или 71234567890 (11-15 цифр).</div>';
    }
    if ($errors['email']) {
        setcookie('email_error', '', time() - 3600);
        $messages[] = '<div class="error">Некорректный email. Введите email в правильном формате.</div>';
    }
    if ($errors['birth_date']) {
        setcookie('birth_date_error', '', time() - 3600);
        $messages[] = '<div class="error">Укажите дату рождения.</div>';
    }
    if ($errors['gender']) {
        setcookie('gender_error', '', time() - 3600);
        $messages[] = '<div class="error">Некорректный выбор пола.</div>';
    }
    if ($errors['languages']) {
        setcookie('languages_error', '', time() - 3600);
        $messages[] = '<div class="error">Выберите хотя бы один язык программирования.</div>';
    }
    if ($errors['bio']) {
        setcookie('bio_error', '', time() - 3600);
        $messages[] = '<div class="error">Заполните биографию.</div>';
    }
    if ($errors['agreement']) {
        setcookie('agreement_error', '', time() - 3600);
        $messages[] = '<div class="error">Вы должны принять условия.</div>';
    }
    
    // Load existing data if editing
    if (isset($_SESSION['user_id'])) {
        try {
            $db = new PDO('mysql:host=localhost;dbname=u68653', 'u68653', '7251537', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            $stmt = $db->prepare("SELECT * FROM applications WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $app = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($app) {
                setcookie('fio_value', $app['fio'], time() + 365 * 24 * 60 * 60);
                setcookie('phone_value', $app['phone'], time() + 365 * 24 * 60 * 60);
                setcookie('email_value', $app['email'], time() + 365 * 24 * 60 * 60);
                setcookie('birth_date_value', $app['birth_date'], time() + 365 * 24 * 60 * 60);
                setcookie('gender_value', $app['gender'], time() + 365 * 24 * 60 * 60);
                setcookie('bio_value', $app['bio'], time() + 365 * 24 * 60 * 60);
                setcookie('agreement_value', $app['agreement'], time() + 365 * 24 * 60 * 60);
                
                $stmt = $db->prepare("SELECT lang_id FROM application_languages WHERE app_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $langs = $stmt->fetchAll(PDO::FETCH_COLUMN);
                setcookie('languages_value', json_encode($langs), time() + 365 * 24 * 60 * 60);
            }
        } catch (PDOException $e) {
            $messages[] = '<div class="error">Ошибка загрузки данных</div>';
        }
    }
    
    include('form.php');
    exit();
}

// Handle form submission
$errors = FALSE;
if (empty($_POST['fio']) || !preg_match('/^[A-Za-zА-Яа-яЁё\s]+$/u', $_POST['fio'])) {
    setcookie('fio_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    setcookie('fio_value', $_POST['fio'], time() + 365 * 24 * 60 * 60);
}

if (empty($_POST['phone']) || !preg_match('/^\+?[0-9]{11,15}$/', $_POST['phone'])) {
    setcookie('phone_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    setcookie('phone_value', $_POST['phone'], time() + 365 * 24 * 60 * 60);
}

if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    setcookie('email_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    setcookie('email_value', $_POST['email'], time() + 365 * 24 * 60 * 60);
}

if (empty($_POST['birth_date'])) {
    setcookie('birth_date_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    setcookie('birth_date_value', $_POST['birth_date'], time() + 365 * 24 * 60 * 60);
}

if (empty($_POST['gender']) || !in_array($_POST['gender'], ['male', 'female'])) {
    setcookie('gender_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    setcookie('gender_value', $_POST['gender'], time() + 365 * 24 * 60 * 60);
}

if (empty($_POST['languages'])) {
    setcookie('languages_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    setcookie('languages_value', json_encode($_POST['languages']), time() + 365 * 24 * 60 * 60);
}

if (empty($_POST['bio'])) {
    setcookie('bio_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    setcookie('bio_value', $_POST['bio'], time() + 365 * 24 * 60 * 60);
}

if (!isset($_POST['agreement'])) {
    setcookie('agreement_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    setcookie('agreement_value', '1', time() + 365 * 24 * 60 * 60);
}

if ($errors) {
    header('Location: index.php');
    exit();
}

try {
    $db = new PDO('mysql:host=localhost;dbname=u68653', 'u68653', '7251537', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    if (isset($_SESSION['user_id'])) {
        // Update existing application
        $stmt = $db->prepare("UPDATE applications SET fio = ?, phone = ?, email = ?, birth_date = ?, gender = ?, bio = ?, agreement = ? WHERE id = ?");
        $stmt->execute([
            $_POST['fio'], $_POST['phone'], $_POST['email'], $_POST['birth_date'], $_POST['gender'], $_POST['bio'], 1, $_SESSION['user_id']
        ]);
        
        // Delete old languages
        $stmt = $db->prepare("DELETE FROM application_languages WHERE app_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        // Insert new languages
        $stmt = $db->prepare("INSERT INTO application_languages (app_id, lang_id) VALUES (?, ?)");
        foreach ($_POST['languages'] as $lang) {
            $stmt->execute([$_SESSION['user_id'], (int)$lang]);
        }
    } else {
        // Create new application with auto-generated credentials
        $login = generateRandomString(8);
        $password = generateRandomString(10);
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("INSERT INTO applications (fio, phone, email, birth_date, gender, bio, agreement, login, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['fio'], $_POST['phone'], $_POST['email'], $_POST['birth_date'], $_POST['gender'], $_POST['bio'], 1, $login, $password_hash
        ]);
        $app_id = $db->lastInsertId();
        
        $stmt = $db->prepare("INSERT INTO application_languages (app_id, lang_id) VALUES (?, ?)");
        foreach ($_POST['languages'] as $lang) {
            $stmt->execute([$app_id, (int)$lang]);
        }
        
        // Store credentials to show to user
        setcookie('credentials', json_encode(['login' => $login, 'password' => $password]), time() + 24 * 60 * 60);
        
        // Auto-login the user
        $_SESSION['user_id'] = $app_id;
    }
} catch (PDOException $e) {
    $messages[] = '<div class="error">Ошибка сохранения данных: ' . htmlspecialchars($e->getMessage()) . '</div>';
    include('form.php');
    exit();
}

setcookie('save', '1', time() + 24 * 60 * 60);
header('Location: index.php');
