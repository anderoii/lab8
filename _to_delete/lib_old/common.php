<?php
// Для PHP 8.0: array_is_list() з'явилась лише у PHP 8.1
if (!function_exists('array_is_list')) {
    function array_is_list(array $array): bool
    {
        return $array === [] || array_keys($array) === range(0, count($array) - 1);
    }
}
// lib/common.php - спільні функції для «Дошки оголошень» (варіант 3)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Межі полів (схема даних)
const TITLE_MIN = 3;
const TITLE_MAX = 100;
const TEXT_MIN = 10;
const TEXT_MAX = 500;
const AUTHOR_MIN = 2;
const AUTHOR_MAX = 50;
const PRICE_MAX = 1000000;

// Екранування для HTML
function e($text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ---------- CSRF ----------
function csrfToken(): string
{
    if (empty($_SESSION['csrf']) || !is_string($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrfValid(): bool
{
    $sent = $_POST['csrf'] ?? null;
    $real = $_SESSION['csrf'] ?? null;
    return is_string($sent) && is_string($real) && hash_equals($real, $sent);
}

// ---------- Повідомлення після перенаправлення ----------
function setFlash(string $message): void
{
    $_SESSION['flash'] = $message;
}

function takeFlash(): ?string
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_string($message) ? $message : null;
}

// ---------- Ідентифікатори ----------
function isValidId($id): bool
{
    return is_string($id) && preg_match('/^[a-f0-9]{16}$/', $id) === 1;
}

function newId(array $ads): string
{
    $used = array_column($ads, 'id');
    do {
        $id = bin2hex(random_bytes(8));
    } while (in_array($id, $used, true));
    return $id;
}

function findIndexById(array $ads, string $id): ?int
{
    foreach ($ads as $i => $ad) {
        if ($ad['id'] === $id) {
            return $i;
        }
    }
    return null;
}

// ---------- Перевірка введення з форми ----------
// Повертає [значення, помилки]
function cleanAdInput(array $post): array
{
    $values = [];
    $errors = [];

    foreach (['title', 'text', 'author', 'price'] as $field) {
        $raw = $post[$field] ?? null;
        if (!is_string($raw)) { // наприклад, title[]=x
            $values[$field] = '';
            $errors[$field] = 'Поле заповнено неправильно.';
            continue;
        }
        $values[$field] = trim($raw);
    }

    $limits = [
        'title'  => ['Заголовок', TITLE_MIN, TITLE_MAX],
        'text'   => ['Текст', TEXT_MIN, TEXT_MAX],
        'author' => ['Автор', AUTHOR_MIN, AUTHOR_MAX],
    ];
    foreach ($limits as $field => [$label, $min, $max]) {
        if (isset($errors[$field])) {
            continue;
        }
        $length = mb_strlen($values[$field]); // mb_strlen для українського тексту
        if ($length < $min || $length > $max) {
            $errors[$field] = "$label: від $min до $max символів.";
        }
    }

    if (!isset($errors['price'])) {
        $price = $values['price'];
        if ($price === '' || !ctype_digit($price) || strlen($price) > 7 || (int) $price > PRICE_MAX) {
            $errors['price'] = 'Ціна: ціле число від 0 до ' . PRICE_MAX . '.';
        }
    }

    return [$values, $errors];
}

// ---------- Перевірка даних, прочитаних з файла ----------
function validateAds($data): array
{
    if (!is_array($data) || !array_is_list($data)) {
        throw new RuntimeException('Очікується список оголошень.');
    }
    $keys = ['id', 'title', 'text', 'author', 'price', 'created'];
    $ids = [];
    $result = [];
    foreach ($data as $i => $ad) {
        $n = $i + 1;
        if (!is_array($ad) || count($ad) !== count($keys)) {
            throw new RuntimeException("Оголошення №$n: неправильний набір полів.");
        }
        foreach ($keys as $key) {
            if (!array_key_exists($key, $ad)) {
                throw new RuntimeException("Оголошення №$n: немає поля $key.");
            }
        }
        if (!isValidId($ad['id'])) {
            throw new RuntimeException("Оголошення №$n: неправильний id.");
        }
        if (isset($ids[$ad['id']])) {
            throw new RuntimeException("Оголошення №$n: id повторюється.");
        }
        $ids[$ad['id']] = true;

        foreach (['title' => [TITLE_MIN, TITLE_MAX], 'text' => [TEXT_MIN, TEXT_MAX], 'author' => [AUTHOR_MIN, AUTHOR_MAX]] as $f => [$min, $max]) {
            if (!is_string($ad[$f]) || mb_strlen($ad[$f]) < $min || mb_strlen($ad[$f]) > $max) {
                throw new RuntimeException("Оголошення №$n: поле $f неправильне.");
            }
        }
        if (!is_int($ad['price']) || $ad['price'] < 0 || $ad['price'] > PRICE_MAX) {
            throw new RuntimeException("Оголошення №$n: ціна неправильна.");
        }
        if (!is_string($ad['created']) || preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $ad['created']) !== 1) {
            throw new RuntimeException("Оголошення №$n: дата неправильна.");
        }
        $result[] = [
            'id' => $ad['id'], 'title' => $ad['title'], 'text' => $ad['text'],
            'author' => $ad['author'], 'price' => $ad['price'], 'created' => $ad['created'],
        ];
    }
    return $result;
}

// ---------- Розмітка сторінок ----------
function pageHeader(string $title): void
{
    echo '<!DOCTYPE html>' . "\n";
    echo '<html lang="uk"><head><meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . e($title) . '</title>';
    echo '<style>body{font-family:Arial,sans-serif;max-width:860px;margin:20px auto;padding:0 12px}'
        . 'table{border-collapse:collapse;width:100%}th,td{border:1px solid #999;padding:6px;text-align:left;vertical-align:top}'
        . '.ok{background:#e3f6e3;padding:8px}.err{background:#fde3e3;padding:8px}.field-err{color:#b00020}'
        . 'label{display:block;margin-top:10px}input[type=text],input[type=number],textarea{width:100%;box-sizing:border-box;padding:6px}</style>';
    echo '</head><body>';
    echo '<h1>' . e($title) . '</h1>';
}

function pageFooter(): void
{
    echo '</body></html>';
}

// Форма додавання / редагування
function renderAdForm(array $values, array $errors, string $action, ?string $id = null): void
{
    echo '<form method="post" action="' . e($action) . '">';
    echo '<input type="hidden" name="csrf" value="' . e(csrfToken()) . '">';
    if ($id !== null) {
        echo '<input type="hidden" name="id" value="' . e($id) . '">';
    }
    $fields = [
        'title'  => ['Заголовок', 'text'],
        'author' => ['Автор', 'text'],
        'price'  => ['Ціна, грн', 'number'],
    ];
    foreach ($fields as $name => [$label, $type]) {
        echo '<label for="' . $name . '">' . $label . '</label>';
        $extra = $type === 'number' ? ' min="0" max="' . PRICE_MAX . '" step="1"' : '';
        echo '<input type="' . $type . '" id="' . $name . '" name="' . $name . '"' . $extra
            . ' value="' . e($values[$name] ?? '') . '">';
        if (isset($errors[$name])) {
            echo '<div class="field-err">' . e($errors[$name]) . '</div>';
        }
    }
    echo '<label for="text">Текст оголошення</label>';
    echo '<textarea id="text" name="text" rows="6">' . e($values['text'] ?? '') . '</textarea>';
    if (isset($errors['text'])) {
        echo '<div class="field-err">' . e($errors['text']) . '</div>';
    }
    echo '<p><button type="submit">Зберегти</button> <a href="index.php">Скасувати</a></p>';
    echo '</form>';
}
