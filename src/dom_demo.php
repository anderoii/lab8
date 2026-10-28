<?php
// Обхід дерева DOM
require __DIR__ . '/common.php';

$dom = new DOMDocument();
$dom->load(__DIR__ . '/persons.xml');
?>
<!DOCTYPE html>
<html lang="uk">
<head><meta charset="UTF-8"><title>Обхід DOM</title></head>
<body>
<h1>Обхід DOM</h1>
<?php foreach ($dom->getElementsByTagName('person') as $person): ?>
<?php
    $name = $person->getElementsByTagName('name')->item(0)->textContent;
    $age  = $person->getElementsByTagName('age')->item(0)->textContent;
?>
<p><?= e($name) ?>, вік: <?= e($age) ?></p>
<?php endforeach; ?>
</body>
</html>
