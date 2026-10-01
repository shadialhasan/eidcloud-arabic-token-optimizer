#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Zero-dependency PHP Automated Test Runner for eidcloud-arabic-token-optimizer.
 */

$rootDir = dirname(__DIR__);

// Autoloader for library and test classes
spl_autoload_register(static function (string $class) use ($rootDir): void {
    if (str_starts_with($class, 'EidCloud\\ArabicTokenOptimizer\\Tests\\')) {
        $relativeClass = substr($class, strlen('EidCloud\\ArabicTokenOptimizer\\Tests\\'));
        $file = $rootDir . '/tests/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    if (str_starts_with($class, 'EidCloud\\ArabicTokenOptimizer\\')) {
        $relativeClass = substr($class, strlen('EidCloud\\ArabicTokenOptimizer\\'));
        $file = $rootDir . '/src/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

require_once __DIR__ . '/TestCase.php';

// ANSI coloring
$hasAnsi = (DIRECTORY_SEPARATOR === '\\')
    ? ((function_exists('sapi_windows_vt100_support') && @sapi_windows_vt100_support(STDOUT)) || getenv('ANSICON') !== false)
    : (function_exists('posix_isatty') && @posix_isatty(STDOUT));

$c = static function (string $text, string $color) use ($hasAnsi): string {
    if (!$hasAnsi) {
        return $text;
    }
    $map = [
        'green' => "\033[32m",
        'red' => "\033[31m",
        'cyan' => "\033[36m",
        'yellow' => "\033[33m",
        'bold' => "\033[1m",
        'dim' => "\033[2m",
        'reset' => "\033[0m",
    ];
    return ($map[$color] ?? '') . $text . ($map['reset'] ?? '');
};

echo "\n" . $c("⚡ EidCloud Arabic Token Optimizer — Automated Test Suite", 'bold') . "\n";
echo $c(str_repeat("=", 70), 'dim') . "\n\n";

$testFiles = glob(__DIR__ . '/*Test.php');
$totalTests = 0;
$totalAssertions = 0;
$passedTests = 0;
$failedTests = 0;
$failures = [];

$startTime = hrtime(true);

foreach ($testFiles as $testFile) {
    require_once $testFile;
    $className = 'EidCloud\\ArabicTokenOptimizer\\Tests\\' . basename($testFile, '.php');

    if (!class_exists($className)) {
        continue;
    }

    $reflection = new ReflectionClass($className);
    if ($reflection->isAbstract()) {
        continue;
    }

    $suiteName = $reflection->getShortName();
    echo $c("• {$suiteName}:", 'cyan') . "\n";

    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

    foreach ($methods as $method) {
        $methodName = $method->getName();
        if (!str_starts_with($methodName, 'test')) {
            continue;
        }

        $totalTests++;
        /** @var \EidCloud\ArabicTokenOptimizer\Tests\TestCase $instance */
        $instance = new $className();
        $instance->resetAssertionsCount();

        if (method_exists($instance, 'setUp')) {
            $instance->setUp();
        }

        $testStart = hrtime(true);
        try {
            $instance->$methodName();
            $testDuration = (hrtime(true) - $testStart) / 1e6;
            $assertions = $instance->getAssertionsCount();
            $totalAssertions += $assertions;
            $passedTests++;

            printf(
                "  %s %-45s %s\n",
                $c("✔", 'green'),
                $methodName,
                $c(sprintf("(%.2f ms, %d assertions)", $testDuration, $assertions), 'dim')
            );
        } catch (\Throwable $t) {
            $testDuration = (hrtime(true) - $testStart) / 1e6;
            $failedTests++;
            $failures[] = [
                'suite' => $suiteName,
                'method' => $methodName,
                'error' => $t->getMessage(),
                'file' => $t->getFile(),
                'line' => $t->getLine(),
                'trace' => $t->getTraceAsString(),
            ];

            printf(
                "  %s %-45s %s\n",
                $c("✖", 'red'),
                $methodName,
                $c(sprintf("(FAILED after %.2f ms)", $testDuration), 'red')
            );
        }
    }
    echo "\n";
}

$totalTimeMs = (hrtime(true) - $startTime) / 1e6;
$memoryPeak = memory_get_peak_usage(true) / (1024 * 1024);

echo $c(str_repeat("=", 70), 'dim') . "\n";

if ($failedTests > 0) {
    echo "\n" . $c("FAILURES ({$failedTests}):", 'red') . "\n";
    foreach ($failures as $idx => $fail) {
        echo "\n" . ($idx + 1) . ") {$fail['suite']}::{$fail['method']}\n";
        echo $c("   " . $fail['error'], 'yellow') . "\n";
        echo $c("   at {$fail['file']}:{$fail['line']}", 'dim') . "\n";
    }
    echo "\n";
}

echo $c("Test Summary:", 'bold') . "\n";
echo "  Tests:      " . ($failedTests === 0 ? $c("{$passedTests} passed", 'green') : $c("{$failedTests} failed", 'red') . ", {$passedTests} passed") . ", {$totalTests} total\n";
echo "  Assertions: {$totalAssertions}\n";
echo "  Duration:   " . sprintf("%.2f ms", $totalTimeMs) . "\n";
echo "  Memory:     " . sprintf("%.2f MB", $memoryPeak) . "\n\n";

if ($failedTests === 0) {
    echo $c("🎉 ALL TESTS PASSED SUCCESSFULLY! (Exit code: 0)", 'green') . "\n\n";
    exit(0);
} else {
    echo $c("❌ TEST SUITE FAILED! (Exit code: 1)", 'red') . "\n\n";
    exit(1);
}
