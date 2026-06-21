<?php
if (($_SERVER['HTTP_X_DEPLOY_TOKEN'] ?? '') !== '__HOOK_TOKEN__') {
    http_response_code(403); exit;
}

chdir(dirname(__DIR__));

function runCmd(string $cmd): array {
    $output = ''; $code = 1;
    if (function_exists('exec')) {
        $lines = []; exec($cmd . ' 2>&1', $lines, $code);
        $output = implode("\n", $lines);
    } elseif (function_exists('shell_exec')) {
        $output = (string) shell_exec($cmd . ' 2>&1');
        $code = 0;
    } elseif (function_exists('passthru')) {
        ob_start(); passthru($cmd . ' 2>&1', $code); $output = ob_get_clean();
    } else {
        $output = 'ERROR: exec/shell_exec/passthru all disabled';
        $code = 1;
    }
    return [$output, $code];
}

$ok = true;
foreach ([
    'composer install --no-dev --optimize-autoloader --no-interaction',
    'php artisan optimize:clear',
    'php artisan migrate --force',
] as $cmd) {
    echo "==> $cmd\n";
    [$out, $code] = runCmd($cmd);
    echo $out . "\n";
    if ($code !== 0) { $ok = false; break; }
}

@unlink(__FILE__);
http_response_code($ok ? 200 : 500);
echo $ok ? "\nDeploy OK" : "\nDeploy FAILED";
