<?php
require __DIR__ . '/../vendor/autoload.php';
$class = '\\Illuminate\\Mail\\Mailable';
$rc = new ReflectionClass($class);
$methods = array_map(function($m){ return $m->name; }, $rc->getMethods());
sort($methods);
foreach($methods as $m) echo $m.PHP_EOL;
?>