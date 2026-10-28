<?php
// Редагування оголошення (JSON)
require __DIR__ . '/../common.php';

$file = __DIR__ . '/data/board.json';
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';

$v = ['title'=>'', 'text'=>'', 'author'=>'', 'price'=>''];
$err = [];
$found = false;

$ads = json_decode(file_get_contents($file), true);

// Шукаємо запис за id
$index = null;
foreach ($ads as $i => $ad) {
    if ($ad['id'] === $id) {
        $index = $i;
    }
}

if ($index === null) {
    http_response_code(404);
    $err['form'] = 'Оголошення не знайдено.';
} else {
    $found = true;
    foreach ($v as $name => $val) {
        $v[$name] = (string)$ads[$index][$name];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrfOk()) {
            http_response_code(403);
            die('CSRF-токен недійсний.');
        }

        [$v, $err] = validateForm();

        if (count($err) == 0) {
            $ads[$index]['title']  = $v['title'];
            $ads[$index]['text']   = $v['text'];
            $ads[$index]['author'] = $v['author'];
            $ads[$index]['price']  = (int)$v['price'];

            file_put_contents($file, json_encode(array_values($ads), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            setFlash('Зміни збережено.');
            header('Location: index.php', true, 303);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head><meta charset="UTF-8"><title>Редагування (JSON)</title></head>
<body>
<h1>Редагування оголошення</h1>
<?php if ($found): ?>
    <?php showForm($v, $err, 'edit.php', $id); ?>
<?php else: ?>
    <p style="color:red"><?= e($err['form']) ?></p>
    <p><a href="index.php">До списку</a></p>
<?php endif; ?>
</body>
</html>
