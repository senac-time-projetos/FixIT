<?php
declare(strict_types=1);

$tests = glob(__DIR__ . '/*_test.php') ?: [];
sort($tests);
$failures = [];
foreach ($tests as $test) {
    $name = basename($test);
    echo "\n=== {$name} ===\n";
    passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($test), $exitCode);
    if ($exitCode !== 0) {
        $failures[] = $name;
    }
}

if ($failures !== []) {
    fwrite(STDERR, "\nFALHA: " . implode(', ', $failures) . "\n");
    exit(1);
}

echo "\nOK: " . count($tests) . " suítes executadas sem falhas.\n";
