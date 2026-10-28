<?php
// Редагування оголошення (XML)
require __DIR__ . '/../common.php';

$file = __DIR__ . '/data/board.xml';
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';

$v = ['title'=>'', 'text'=>'', 'author'=>'', 'price'=>''];
$err = [];
$found = false;

$dom = new DOMDocument();
$dom->load($file);
$dom->formatOutput = true;

// Шукаємо оголошення за id
$adNode = null;
foreach ($dom->getElementsByTagName('ad') as $node) {
    if ($node->getElementsByTagName('id')->item(0)->textContent === $id) {
        $adNode = $node;
    }
}

if ($adNode === null) {
    http_response_code(404);
    $err['form'] = 'Оголошення не знайдено.';
} else {
    $found = true;
    // Заповнюємо поточні значення
    foreach ($v as $name => $val) {
        $v[$name] = $adNode->getElementsByTagName($name)->item(0)->textContent;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrfOk()) {
            http_response_code(403);
            die('CSRF-токен недійсний.');
        }

        [$v, $err] = validateForm();

        if (count($err) == 0) {
            // Оновлюємо поля
            foreach (['title','text','author','price'] as $name) {
                $el = $adNode->getElementsByTagName($name)->item(0);
                $el->textContent = '';
                $el->appendChild($dom->createTextNode($v[$name]));
            }
            $dom->save($file);

            setFlash('Зміни збережено.');
            header('Location: index.php', true, 303);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head><meta charset="UTF-8"><title>Редагування (XML)</title></head>
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
