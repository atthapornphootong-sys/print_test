<?php
require_once __DIR__ . '/database.php';
$cols = executeQuery("DESCRIBE printers");
print_r($cols);
