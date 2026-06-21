<?php
/**
 * Webhook de deploy — llamado por GitHub Actions via curl.
 * El servidor ejecuta git fetch + artisan internamente.
 *
 * Requiere en .env del servidor: DEPLOY_SECRET=tu_clave_secreta
 */

$token  = getenv('DEPLOY_SECRET');
$header = $_SERVER['HTTP_X_DEPLOY_TOKEN'] ?? '';

if ($token && !hash_equals($token, $header)) {
    http_response_code(403);
    die(json_encode(['error' => 'Unauthorized']));
}

$appPath = realpath(__DIR__ . '/..');

function run(string $cmd, string $cwd): array
{
    $output = []; $code = 0;
    exec("cd {$cwd} && {$cmd} 2>&1", $output, $code);
    return ['cmd' => $cmd, 'output' => implode("\n", $output), 'code' => $code];
}

$results = [];
$results[] = run('git fetch https://github.com/Mofio2021/barberortiz.git main', $appPath);
$results[] = run('git checkout -f FETCH_HEAD -- .', $appPath);
$results[] = run('composer install --no-dev --optimize-autoloader --no-interaction', $appPath);
$results[] = run('php artisan optimize:clear', $appPath);
$results[] = run('php artisan migrate --force', $appPath);

$success = array_reduce($results, fn($carry, $r) => $carry && $r['code'] === 0, true);

http_response_code($success ? 200 : 500);
header('Content-Type: application/json');
echo json_encode(['success' => $success, 'steps' => $results], JSON_PRETTY_PRINT);
