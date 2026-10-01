<?php

declare(strict_types=1);

namespace EidCloud\ArabicTokenOptimizer\Tests;

/**
 * Base zero-dependency test case with comprehensive assertions.
 */
abstract class TestCase
{
    private int $assertionsCount = 0;

    public function getAssertionsCount(): int
    {
        return $this->assertionsCount;
    }

    public function resetAssertionsCount(): void
    {
        $this->assertionsCount = 0;
    }

    protected function assertEquals(mixed $expected, mixed $actual, string $message = ''): void
    {
        $this->assertionsCount++;
        if ($expected != $actual) {
            $msg = $message !== '' ? $message : sprintf(
                "Failed asserting that actual %s matches expected %s.",
                var_export($actual, true),
                var_export($expected, true)
            );
            throw new \AssertionError($msg);
        }
    }

    protected function assertSame(mixed $expected, mixed $actual, string $message = ''): void
    {
        $this->assertionsCount++;
        if ($expected !== $actual) {
            $msg = $message !== '' ? $message : sprintf(
                "Failed asserting that actual %s is strictly identical to %s.",
                var_export($actual, true),
                var_export($expected, true)
            );
            throw new \AssertionError($msg);
        }
    }

    protected function assertNotSame(mixed $expected, mixed $actual, string $message = ''): void
    {
        $this->assertionsCount++;
        if ($expected === $actual) {
            $msg = $message !== '' ? $message : sprintf(
                "Failed asserting that actual %s is not identical to %s.",
                var_export($actual, true),
                var_export($expected, true)
            );
            throw new \AssertionError($msg);
        }
    }

    protected function assertTrue(bool $condition, string $message = ''): void
    {
        $this->assertionsCount++;
        if (!$condition) {
            throw new \AssertionError($message !== '' ? $message : "Failed asserting that condition is true.");
        }
    }

    protected function assertFalse(bool $condition, string $message = ''): void
    {
        $this->assertionsCount++;
        if ($condition) {
            throw new \AssertionError($message !== '' ? $message : "Failed asserting that condition is false.");
        }
    }

    protected function assertGreaterThan(int|float $expected, int|float $actual, string $message = ''): void
    {
        $this->assertionsCount++;
        if ($actual <= $expected) {
            $msg = $message !== '' ? $message : sprintf(
                "Failed asserting that actual %s is greater than %s.",
                $actual,
                $expected
            );
            throw new \AssertionError($msg);
        }
    }

    protected function assertLessThan(int|float $expected, int|float $actual, string $message = ''): void
    {
        $this->assertionsCount++;
        if ($actual >= $expected) {
            $msg = $message !== '' ? $message : sprintf(
                "Failed asserting that actual %s is less than %s.",
                $actual,
                $expected
            );
            throw new \AssertionError($msg);
        }
    }

    protected function assertStringContainsString(string $needle, string $haystack, string $message = ''): void
    {
        $this->assertionsCount++;
        if (!str_contains($haystack, $needle)) {
            $msg = $message !== '' ? $message : sprintf(
                "Failed asserting that string '%s' contains '%s'.",
                $haystack,
                $needle
            );
            throw new \AssertionError($msg);
        }
    }

    protected function assertMatchesRegularExpression(string $pattern, string $string, string $message = ''): void
    {
        $this->assertionsCount++;
        if (!preg_match($pattern, $string)) {
            $msg = $message !== '' ? $message : sprintf(
                "Failed asserting that '%s' matches regular expression '%s'.",
                $string,
                $pattern
            );
            throw new \AssertionError($msg);
        }
    }

    protected function assertCount(int $expectedCount, \Countable|array $countable, string $message = ''): void
    {
        $this->assertionsCount++;
        $actualCount = count($countable);
        if ($actualCount !== $expectedCount) {
            $msg = $message !== '' ? $message : sprintf(
                "Failed asserting that count %d matches expected %d.",
                $actualCount,
                $expectedCount
            );
            throw new \AssertionError($msg);
        }
    }
}
