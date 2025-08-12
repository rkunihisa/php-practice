<?php
header('Content-Type: application/json; charset=utf-8');

$name = isset($_GET['name']) ? $_GET['name'] : 'no name';

echo json_encode([
    'message' => "Hello, {$name}!"
]);
?>
