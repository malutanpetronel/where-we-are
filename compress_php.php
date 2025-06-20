<?php
function compress_php_files($directory) {
    $files = glob("{$directory}/*.php");
    foreach ($files as $file) {
        $content = php_strip_whitespace($file);
        file_put_contents($file, $content);
    }
}

// Specifică directorul `dist` pentru fișierele PHP minimizate
compress_php_files(__DIR__ . '/dist');
