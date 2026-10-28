<?php
// Додавання оголошення (JSON)
require __DIR__ . '/../common.php';

$v = ['title'=>'', 'text'=>'', 'author'=>'', 'price'=>''];
$err = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfOk()) {
        http_response_code(403);
        die('CSRF-токен недійсний.');
    }

    [$v, $err] = validateForm();

    if (count($err) == 0) {
        $file = __DIR__ . '/data/board.json';
        $ads = json_decode(file_get_contents($file), true);

        // Додаємо новий запис
        $ads[] = [
            'id'      => bin2hex(random_bytes(8)),
            'title'   => $v['title'],
            'text'    => $v['text'],
            'author'  => $v['author'],
            'price'   => (int)$v['price'],
            'created' => date('Y-m-d H:i'),
        ];

        file_put_contents($file, json_encode($ads, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        setFlash('Оголошення додано.');
        header('Location: index.php', true, 303);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head><meta charset="UTF-8"><title>Нове оголошення (JSON)</title></head>
<body>
<h1>Нове оголошення</h1>
<?php showForm($v, $err, 'add.php'); ?>
</body>
</html>
