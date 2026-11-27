<?php
declare(strict_types=1);

if ($argc > 2 || ($argc === 2 && $argv[1] !== '--local')) {
    fwrite(STDERR, "Uso: php tests/run_all.php [--local]\n");
    exit(2);
}

$localOnly = $argc === 2;
$hasPostgresUrl = (bool) getenv('DATABASE_URL');
$postgresTests = ['postgresql_application_flow_test.php', 'postgresql_hosted_test.php'];
$passed = 0;
$skipped = 0;
$tests = glob(__DIR__ . '/*_test.php') ?: [];
sort($tests);
$failures = [];
foreach ($tests as $test) {
    $name = basename($test);
    echo "\n=== {$name} ===\n";
    if ($localOnly && (str_starts_with($name, 'docker_') || in_array($name, $postgresTests, true))) {
        echo "SKIP: suíte de integração com banco externo.\n";
        $skipped++;
        continue;
    }
    if (!$hasPostgresUrl && in_array($name, $postgresTests, true)) {
        echo "SKIP: DATABASE_URL não definida.\n";
        $skipped++;
        continue;
    }

    passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($test), $exitCode);
    if ($exitCode !== 0) {
        $failures[] = $name;
        continue;
    }
    $passed++;
}

if ($failures !== []) {
    fwrite(STDERR, "\nFALHA: " . implode(', ', $failures) . "\n");
    exit(1);
}

echo "\nOK: " . $passed . " suítes aprovadas; " . $skipped . " ignoradas.\n";
