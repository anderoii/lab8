<?php
// Перегляд оголошень (XML)
require __DIR__ . '/../common.php';

$file = __DIR__ . '/data/board.xml';
$dom = new DOMDocument();
$dom->load($file);

// Збираємо оголошення з XML
$ads = [];
foreach ($dom->getElementsByTagName('ad') as $node) {
    $ad = [];
    foreach (['id','title','text','author','price','created'] as $name) {
        $ad[$name] = $node->getElementsByTagName($name)->item(0)->textContent;
    }
    $ads[] = $ad;
}

// Сортуємо — нові зверху
usort($ads, function($a, $b) {
    return strcmp($b['created'], $a['created']);
});
?>
<!DOCTYPE html>
<html lang="uk">
<head><meta charset="UTF-8"><title>Дошка оголошень (XML)</title></head>
<body>
<h1>Дошка оголошень (XML)</h1>
<?php
$flash = getFlash();
if ($flash) echo '<p style="background:#dfd;padding:6px">' . e($flash) . '</p>';
?>
<p><a href="add.php">Додати оголошення</a></p>
<?php showAds($ads); ?>
<p><a href="../../index.html">На головну</a></p>
</body>
</html>
