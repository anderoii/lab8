<?php
// lib/storage_json.php - сховище оголошень у JSON

function storageName(): string
{
    return 'JSON';
}

function dataPath(): string
{
    return __DIR__ . '/../json/data/board.json';
}

function loadAds(): array
{
    $path = dataPath();
    if (!is_file($path) || !is_readable($path)) {
        throw new RuntimeException('Файл даних не знайдено або він недоступний.');
    }
    $json = file_get_contents($path);
    if ($json === false || trim($json) === '') {
        throw new RuntimeException('Файл даних порожній або не читається.');
    }
    try {
        $check = json_decode($json, false, 512, JSON_THROW_ON_ERROR); // корінь має бути масивом, а не об'єктом
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        throw new RuntimeException('Некоректний JSON або UTF-8.');
    }
    if (!is_array($check)) {
        throw new RuntimeException('Очікується JSON-масив, а не об’єкт.');
    }
    return validateAds($data);
}

function saveAds(array $ads): void
{
    try {
        $json = json_encode(array_values(validateAds($ads)),
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        throw new RuntimeException('Не вдалося сформувати JSON.');
    }
    $content = $json . "\n";
    $bytes = file_put_contents(dataPath(), $content, LOCK_EX);
    if ($bytes === false || $bytes !== strlen($content)) {
        throw new RuntimeException('Не вдалося повністю записати файл даних.');
    }
}
