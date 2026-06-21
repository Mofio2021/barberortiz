<?php
if (($_SERVER['HTTP_X_DEPLOY_TOKEN'] ?? '') !== '__HOOK_TOKEN__') {
    http_response_code(403); exit;
}
chdir(dirname(__DIR__));
$ok = true;
foreach ([
    'composer install --no-dev --optimize-autoloader --no-interaction',
    'php artisan optimize:clear',
    'php artisan migrate --force',
] as $cmd) {
    echo "==> $cmd\n";
    passthru($cmd . ' 2>&1', $code);
    if ($code !== 0) { $ok = false; break; }
}
@unlink(__FILE__);
http_response_code($ok ? 200 : 500);
