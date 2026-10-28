<?php
// Генерація файла persons.xml
$dom = new DOMDocument('1.0', 'UTF-8');
$dom->formatOutput = true;

$root = $dom->createElement('people');
$dom->appendChild($root);

$data = [
    ['name' => 'Олена', 'age' => 20],
    ['name' => 'Тарас', 'age' => 25],
    ['name' => 'Клуб <Код> & друзі', 'age' => 0],
];

foreach ($data as $row) {
    $person = $dom->createElement('person');

    $name = $dom->createElement('name');
    $name->appendChild($dom->createTextNode($row['name']));
    $person->appendChild($name);

    $age = $dom->createElement('age');
    $age->appendChild($dom->createTextNode($row['age']));
    $person->appendChild($age);

    $root->appendChild($person);
}

$dom->save(__DIR__ . '/persons.xml');
echo "persons.xml створено.\n";
