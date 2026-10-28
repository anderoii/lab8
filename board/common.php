<?php
session_start();

// Екранування
function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// CSRF-токен
function csrfToken() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}
function csrfOk() {
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && $_POST['csrf'] === $_SESSION['csrf'];
}

// Флеш-повідомлення
function setFlash($msg) {
    $_SESSION['flash'] = $msg;
}
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $msg = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $msg;
    }
    return '';
}

// Таблиця оголошень
function showAds($ads) {
    if (count($ads) == 0) {
        echo '<p>Записів немає.</p>';
        return;
    }
    echo '<table border="1" cellpadding="5">';
    echo '<tr><th>Заголовок</th><th>Текст</th><th>Автор</th><th>Ціна</th><th>Дата</th><th></th></tr>';
    foreach ($ads as $ad) {
        echo '<tr>';
        echo '<td>' . e($ad['title']) . '</td>';
        echo '<td>' . e($ad['text']) . '</td>';
        echo '<td>' . e($ad['author']) . '</td>';
        echo '<td>' . e($ad['price']) . '</td>';
        echo '<td>' . e($ad['created']) . '</td>';
        echo '<td><a href="edit.php?id=' . urlencode($ad['id']) . '">Редагувати</a></td>';
        echo '</tr>';
    }
    echo '</table>';
}

// Валідація полів форми
function validateForm() {
    $v = [];
    $err = [];
    foreach (['title','text','author','price'] as $name) {
        $v[$name] = isset($_POST[$name]) ? trim($_POST[$name]) : '';
    }
    if (mb_strlen($v['title']) < 3 || mb_strlen($v['title']) > 100)
        $err['title'] = 'Заголовок: від 3 до 100 символів.';
    if (mb_strlen($v['text']) < 10 || mb_strlen($v['text']) > 500)
        $err['text'] = 'Текст: від 10 до 500 символів.';
    if (mb_strlen($v['author']) < 2 || mb_strlen($v['author']) > 50)
        $err['author'] = 'Автор: від 2 до 50 символів.';
    if (!ctype_digit($v['price']) || (int)$v['price'] > 1000000)
        $err['price'] = 'Ціна: ціле число від 0 до 1000000.';
    return [$v, $err];
}

// Форма додавання/редагування
function showForm($v, $err, $action, $id = '') {
    echo '<form method="post" action="' . e($action) . '">';
    echo '<input type="hidden" name="csrf" value="' . e(csrfToken()) . '">';
    if ($id !== '') {
        echo '<input type="hidden" name="id" value="' . e($id) . '">';
    }

    $fields = ['title' => 'Заголовок', 'author' => 'Автор', 'price' => 'Ціна, грн'];
    foreach ($fields as $name => $label) {
        echo '<p>' . $label . ':<br>';
        echo '<input type="text" name="' . $name . '" size="60" value="' . e($v[$name]) . '">';
        if (isset($err[$name])) echo ' <span style="color:red">' . e($err[$name]) . '</span>';
        echo '</p>';
    }
    echo '<p>Текст оголошення:<br>';
    echo '<textarea name="text" rows="5" cols="60">' . e($v['text']) . '</textarea>';
    if (isset($err['text'])) echo ' <span style="color:red">' . e($err['text']) . '</span>';
    echo '</p>';

    echo '<p><button type="submit">Зберегти</button> <a href="index.php">Скасувати</a></p>';
    echo '</form>';
}
