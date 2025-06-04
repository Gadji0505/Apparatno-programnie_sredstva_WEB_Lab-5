<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();

function setValue($field) {
    return isset($_COOKIE[$field]) ? htmlspecialchars($_COOKIE[$field]) : '';
}

function setChecked($field, $value) {
    return (isset($_COOKIE[$field]) && $_COOKIE[$field] == $value) ? 'checked' : '';
}

function setSelected($field, $value) {
    return (isset($_COOKIE[$field]) && in_array($value, (array)$_COOKIE[$field])) ? 'selected' : '';
}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Check if user is logged in
$logged_in = isset($_SESSION['user_id']);
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

// Database connection
try {
    $db = new PDO('mysql:host=localhost;dbname=u68653', 'u68653', '7251537', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die('Ошибка подключения к базе данных: ' . $e->getMessage());
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $db->prepare("SELECT * FROM users WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['login'] = $user['login'];
        $_SESSION['is_admin'] = $user['is_admin'];
        header('Location: index.php');
        exit();
    } else {
        $login_error = 'Неверный логин или пароль';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $messages = array();
    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', 100000);
        $messages[] = 'Данные сохранены!';
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
        setcookie('fio_error', '', 100000);
        $messages[] = '<div class="error">Некорректное ФИО. Допустимы только буквы и пробелы.</div>';
    }
    if ($errors['phone']) {
        setcookie('phone_error', '', 100000);
        $messages[] = '<div class="error">Некорректный номер телефона. Допустимый формат: +71234567890 или 71234567890 (11-15 цифр).</div>';
    }
    if ($errors['email']) {
        setcookie('email_error', '', 100000);
        $messages[] = '<div class="error">Некорректный email. Введите email в правильном формате.</div>';
    }
    if ($errors['birth_date']) {
        setcookie('birth_date_error', '', 100000);
        $messages[] = '<div class="error">Укажите дату рождения.</div>';
    }
    if ($errors['gender']) {
        setcookie('gender_error', '', 100000);
        $messages[] = '<div class="error">Некорректный выбор пола.</div>';
    }
    if ($errors['languages']) {
        setcookie('languages_error', '', 100000);
        $messages[] = '<div class="error">Выберите хотя бы один язык программирования.</div>';
    }
    if ($errors['bio']) {
        setcookie('bio_error', '', 100000);
        $messages[] = '<div class="error">Заполните биографию.</div>';
    }
    if ($errors['agreement']) {
        setcookie('agreement_error', '', 100000);
        $messages[] = '<div class="error">Вы должны принять условия.</div>';
    }
    
    include('form.php');
    exit();
}

// Handle main form submission
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
    setcookie('languages_value', serialize($_POST['languages']), time() + 365 * 24 * 60 * 60);
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
    // Insert application data
    $stmt = $db->prepare("INSERT INTO applications (fio, phone, email, birth_date, gender, bio, agreement) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['fio'], $_POST['phone'], $_POST['email'], $_POST['birth_date'], $_POST['gender'], $_POST['bio'], 1
    ]);
    $app_id = $db->lastInsertId();
    
    // Insert languages
    $stmt = $db->prepare("INSERT INTO application_languages (app_id, lang_id) VALUES (?, ?)");
    foreach ($_POST['languages'] as $lang) {
        $stmt->execute([$app_id, (int)$lang]);
    }
    
    // Generate and save credentials for the first submission
    if (!isset($_COOKIE['credentials_generated'])) {
        $login = generateRandomString(8);
        $password = generateRandomString(10);
        $password_hash = hashPassword($password);
        
        $stmt = $db->prepare("INSERT INTO users (login, password_hash, app_id) VALUES (?, ?, ?)");
        $stmt->execute([$login, $password_hash, $app_id]);
        
        setcookie('credentials_generated', '1', time() + 365 * 24 * 60 * 60);
        setcookie('generated_login', $login, time() + 24 * 60 * 60);
        setcookie('generated_password', $password, time() + 24 * 60 * 60);
    }
} catch (PDOException $e) {
    echo 'Ошибка: ' . $e->getMessage();
    exit();
}

setcookie('save', '1');
header('Location: index.php');