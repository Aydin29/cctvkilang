<?php
$path = realpath(__DIR__ . '/../public/images/PPN3.png');
echo "project_dir=" . realpath(__DIR__ . '/../') . PHP_EOL;
echo "logo_path=" . ($path?:'MISSING') . PHP_EOL;
echo "file_exists=" . (file_exists(__DIR__ . '/../public/images/PPN3.png') ? 'yes' : 'no') . PHP_EOL;
?>