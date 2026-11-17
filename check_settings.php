<?php
require_once __DIR__ . '/database.php';
$res = executeQuery("SELECT * FROM settings");
print_r($res);
