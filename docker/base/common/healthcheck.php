#!/usr/bin/env php
<?php

declare(strict_types=1);

function logMessage(string $level, string $message): void
{
    fwrite(STDERR, sprintf("%s [healthcheck] %s: %s\n", gmdate('c'), $level, $message));
}

function fail(string $message, int $code = 1): never
{
    logMessage('ERROR', $message);
    exit($code);
}

function run(string $command): array
{
    $output = [];
    $exitCode = 0;
    exec($command . ' 2>&1', $output, $exitCode);

    return [$exitCode, trim(implode("\n", $output))];
}

$mode = $argv[1] ?? 'app';
$rootPath = '/var/www/html';
$artisanPath = $rootPath . '/artisan';

if (!is_dir($rootPath)) {
    fail("required path is missing: {$rootPath}", 2);
}

/*
 * Only enforce Laravel directory checks when this is a Laravel project.
 * For infra-only/template repositories, app health should rely on process liveness.
 */
if (is_file($artisanPath)) {
    $requiredPaths = [
        $rootPath . '/public',
        $rootPath . '/bootstrap/cache',
        $rootPath . '/storage',
    ];

    foreach ($requiredPaths as $path) {
        if (!is_dir($path)) {
            fail("required path is missing: {$path}", 2);
        }
    }
}

if ($mode === 'app') {
    [$code, $output] = run('pgrep -f "php-fpm: master process" >/dev/null && echo ok || echo fail');
    if ($code !== 0 || $output !== 'ok') {
        fail('php-fpm master process is not running', 3);
    }

    exit(0);
}

$processPatterns = [
    'queue' => 'artisan queue:work',
    'scheduler' => 'artisan schedule:work',
];

if (!isset($processPatterns[$mode])) {
    fail("unknown healthcheck mode: {$mode}", 4);
}

$pattern = escapeshellarg($processPatterns[$mode]);
[$code, $output] = run("pgrep -f {$pattern} >/dev/null && echo ok || echo fail");
if ($code !== 0 || $output !== 'ok') {
    fail("expected process is not running for mode: {$mode}", 5);
}

exit(0);
