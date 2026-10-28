<?php
// Читання даних із XML-файла
require __DIR__ . '/common.php';

$dom = new DOMDocument();
$dom->load(__DIR__ . '/persons.xml');

$people = [];
foreach ($dom->getElementsByTagName('person') as $person) {
    $name = $person->getElementsByTagName('name')->item(0)->textContent;
    $age  = $person->getElementsByTagName('age')->item(0)->textContent;
    $people[] = ['name' => $name, 'age' => $age];
}
?>
<!DOCTYPE html>
<html lang="uk">
<head><meta charset="UTF-8"><title>Читання XML</title></head>
<body>
<h1>Список із persons.xml</h1>
<table border="1" cellpadding="5">
<tr><th>№</th><th>Ім'я</th><th>Вік</th></tr>
<?php foreach ($people as $i => $p): ?>
<tr>
    <td><?= $i + 1 ?></td>
    <td><?= e($p['name']) ?></td>
    <td><?= e($p['age']) ?></td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>
