<?php
$dirs = ['app/Filament', 'resources/views/reports', 'app/Console', 'database/seeders', 'app/Models'];

function replaceInDir($dir) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getRealPath());
            $newContent = str_replace(
                ["Setting::get('currency', 'USD')", "Setting::get('currency', '$')", "Setting::get('currency', 'HTG')"],
                "Setting::get('currency', 'Gourdes')",
                $content
            );
            $newContent = str_replace(
                ["'key' => 'currency', 'value' => '$'"],
                ["'key' => 'currency', 'value' => 'Gourdes'"],
                $newContent
            );
            if ($newContent !== $content) {
                file_put_contents($file->getRealPath(), $newContent);
                echo "Updated {$file->getRealPath()}\n";
            }
        }
    }
}

foreach ($dirs as $dir) {
    replaceInDir(__DIR__ . '/' . $dir);
}
echo "Done\n";
