<?php
// Додавання оголошення (XML)
require __DIR__ . '/../common.php';

$v = ['title'=>'', 'text'=>'', 'author'=>'', 'price'=>''];
$err = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Перевірка CSRF
    if (!csrfOk()) {
        http_response_code(403);
        die('CSRF-токен недійсний.');
    }

    [$v, $err] = validateForm();

    if (count($err) == 0) {
        // Читаємо файл
        $file = __DIR__ . '/data/board.xml';
        $dom = new DOMDocument();
        $dom->load($file);
        $dom->formatOutput = true;

        // Створюємо новий елемент
        $ad = $dom->createElement('ad');
        $fields = [
            'id'      => bin2hex(random_bytes(8)),
            'title'   => $v['title'],
            'text'    => $v['text'],
            'author'  => $v['author'],
            'price'   => $v['price'],
            'created' => date('Y-m-d H:i'),
        ];
        foreach ($fields as $name => $value) {
            $el = $dom->createElement($name);
            $el->appendChild($dom->createTextNode($value));
            $ad->appendChild($el);
        }
        $dom->documentElement->appendChild($ad);
        $dom->save($file);

        setFlash('Оголошення додано.');
        header('Location: index.php', true, 303);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head><meta charset="UTF-8"><title>Нове оголошення (XML)</title></head>
<body>
<h1>Нове оголошення</h1>
<?php showForm($v, $err, 'add.php'); ?>
</body>
</html>
