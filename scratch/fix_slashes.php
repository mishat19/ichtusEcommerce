<?php
$dirs = ['view', 'backoffice/view'];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $file) {
        if ($file->isDir()) continue;
        if ($file->getExtension() === 'php') {
            $path = $file->getRealPath();
            $content = file_get_contents($path);
            // On cherche action="/quelque-chose" et on ajoute un / à la fin
            // On évite ceux qui ont déjà un / ou un point (ex: /css/style.css)
            $new = preg_replace('/action="\/([a-zA-Z0-9-]+)"/', 'action="/$1/"', $content);
            if ($new !== $content) {
                file_put_contents($path, $new);
                echo "Fixed $path\n";
            }
        }
    }
}
