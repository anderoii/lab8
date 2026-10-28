<?php
// lib/storage_xml.php - сховище оголошень у XML (DOM)

function storageName(): string
{
    return 'XML';
}

function dataPath(): string
{
    return __DIR__ . '/../xml/data/board.xml';
}

// Читання всіх оголошень з перевіркою структури
function loadAds(): array
{
    $path = dataPath();
    if (!is_file($path) || !is_readable($path)) {
        throw new RuntimeException('Файл даних не знайдено або він недоступний.');
    }
    $xml = file_get_contents($path);
    if ($xml === false || trim($xml) === '') {
        throw new RuntimeException('Файл даних порожній або не читається.');
    }

    $oldMode = libxml_use_internal_errors(true);
    libxml_clear_errors();
    $dom = new DOMDocument('1.0', 'UTF-8');
    $ok = $dom->loadXML($xml, LIBXML_NONET); // без LIBXML_NOENT і LIBXML_DTDLOAD
    $errors = libxml_get_errors();
    libxml_clear_errors();
    libxml_use_internal_errors($oldMode);

    if (!$ok) {
        $message = $errors ? trim($errors[0]->message) : 'невідома помилка';
        throw new RuntimeException('Некоректний XML: ' . $message);
    }
    if ($dom->doctype !== null) {
        throw new RuntimeException('XML із DTD не допускається.');
    }
    $root = $dom->documentElement;
    if ($root === null || $root->nodeName !== 'board') {
        throw new RuntimeException('Кореневий елемент має бути <board>.');
    }

    $ads = [];
    foreach ($root->childNodes as $node) {
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            continue;
        }
        if ($node->nodeName !== 'ad') {
            throw new RuntimeException('Усередині <board> дозволені тільки <ad>.');
        }
        $fields = [];
        foreach ($node->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }
            if (isset($fields[$child->nodeName])) {
                throw new RuntimeException('Повторюване поле <' . $child->nodeName . '>.');
            }
            $fields[$child->nodeName] = $child->textContent;
        }
        if (isset($fields['price'])) {
            if (preg_match('/^\d{1,7}$/', $fields['price']) !== 1) {
                throw new RuntimeException('Ціна в XML має бути цілим числом.');
            }
            $fields['price'] = (int) $fields['price'];
        }
        $ads[] = $fields;
    }
    return validateAds($ads);
}

// Збереження: будуємо дерево DOM і записуємо весь документ
function saveAds(array $ads): void
{
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->formatOutput = true;
    $root = $dom->createElement('board');
    $dom->appendChild($root);

    foreach (validateAds($ads) as $ad) {
        $item = $dom->createElement('ad');
        foreach ($ad as $tag => $value) {
            $field = $dom->createElement($tag); // імена тегів задаємо кодом
            $field->appendChild($dom->createTextNode((string) $value)); // значення - текстовий вузол
            $item->appendChild($field);
        }
        $root->appendChild($item);
    }

    $content = $dom->saveXML();
    if ($content === false) {
        throw new RuntimeException('Не вдалося сформувати XML.');
    }
    $bytes = file_put_contents(dataPath(), $content, LOCK_EX);
    if ($bytes === false || $bytes !== strlen($content)) {
        throw new RuntimeException('Не вдалося повністю записати файл даних.');
    }
}
