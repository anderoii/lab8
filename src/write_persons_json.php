<?php
// Генерація файла persons.json
$data = [
    ['name' => 'Олена', 'age' => 20],
    ['name' => 'Тарас', 'age' => 25],
    ['name' => 'Клуб <Код> & друзі', 'age' => 0],
];

$json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
file_put_contents(__DIR__ . '/persons.json', $json . "\n");
echo "persons.json створено.\n";
