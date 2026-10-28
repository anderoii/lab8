<?php
// Читання даних із JSON-файла
require __DIR__ . '/common.php';

$json = file_get_contents(__DIR__ . '/persons.json');
$people = json_decode($json, true);
?>
<!DOCTYPE html>
<html lang="uk">
<head><meta charset="UTF-8"><title>Читання JSON</title></head>
<body>
<h1>Список із persons.json</h1>
<table border="1" cellpadding="5">
<tr><th>№</th><th>Ім'я</th><th>Вік</th></tr>
<?php foreach ($people as $i => $p): ?>
<tr>
    <td><?= $i + 1 ?></td>
    <td><?= e($p['name']) ?></td>
    <td><?= $p['age'] ?></td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>
