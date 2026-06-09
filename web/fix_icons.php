<?php
$dir = new RecursiveDirectoryIterator("resources/views");
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, "/\.blade\.php$/");
$count = 0;
foreach($files as $file) {
    $path = $file->getPathname();
    $content = file_get_contents($path);
    // Replace class="ti-icon..." with class="ti ti-icon..."
    $newContent = preg_replace('/class=["\']ti-([a-zA-Z0-9\-]+)([^"\']*)["\']/', 'class="ti ti-$1$2"', $content);
    if ($content !== $newContent) {
        file_put_contents($path, $newContent);
        $count++;
        echo "Updated: $path\n";
    }
}
echo "Total updated files for icons: $count\n";
