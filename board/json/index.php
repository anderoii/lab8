<?php
// Перегляд оголошень (JSON)
require __DIR__ . '/../common.php';

$file = __DIR__ . '/data/board.json';
$json = file_get_contents($file);
$ads = json_decode($json, true);

// Сортуємо — нові зверху
usort($ads, function($a, $b) {
    return strcmp($b['created'], $a['created']);
});
?>
<!DOCTYPE html>
<html lang="uk">
<head><meta charset="UTF-8"><title>Дошка оголошень (JSON)</title></head>
<body>
<h1>Дошка оголошень (JSON)</h1>
<?php
$flash = getFlash();
if ($flash) echo '<p style="background:#dfd;padding:6px">' . e($flash) . '</p>';
?>
<p><a href="add.php">Додати оголошення</a></p>
<?php showAds($ads); ?>
<p><a href="../../index.html">На головну</a></p>
</body>
</html>
