<?php

declare(strict_types=1);

namespace EidCloud\ArabicTokenOptimizer\Tests;

class CliTest extends TestCase
{
    private string $binPath;

    public function setUp(): void
    {
        $this->binPath = escapeshellarg(dirname(__DIR__) . '/bin/eidcloud-arabic-opt');
    }

    public function testVersionCommand(): void
    {
        $cmd = "php {$this->binPath} --version";
        exec($cmd, $output, $exitCode);

        $this->assertSame(0, $exitCode);
        $fullOutput = implode("\n", $output);
        $this->assertStringContainsString('eidcloud-arabic-token-optimizer v1.0.0', $fullOutput);
    }

    public function testHelpCommand(): void
    {
        $cmd = "php {$this->binPath} --help";
        exec($cmd, $output, $exitCode);

        $this->assertSame(0, $exitCode);
        $fullOutput = implode("\n", $output);
        $this->assertStringContainsString('USAGE:', $fullOutput);
        $this->assertStringContainsString('OPTIONS:', $fullOutput);
    }

    public function testCompressStringWithJsonOutput(): void
    {
        $text = escapeshellarg("مـــــرحـــــبـــــاً بِــــكُـــــمْ");
        $cmd = "php {$this->binPath} compress {$text} --json";
        exec($cmd, $output, $exitCode);

        $this->assertSame(0, $exitCode);
        $jsonStr = implode("\n", $output);
        $data = json_decode($jsonStr, true);

        $this->assertSame(JSON_ERROR_NONE, json_last_error(), "CLI output must be valid JSON");
        $this->assertSame("مرحبا بكم", $data['output']['optimized_text']);
        $this->assertGreaterThan(0, $data['metrics']['tokens']['saved']);
    }

    public function testCompressFileWithOutFlag(): void
    {
        $tempInput = sys_get_temp_dir() . '/eidcloud_test_in_' . uniqid() . '.txt';
        $tempOutput = sys_get_temp_dir() . '/eidcloud_test_out_' . uniqid() . '.txt';

        file_put_contents($tempInput, "أحمد ذهب إلى المدرسةِ الجميلةِ !!!!!!!");

        $argIn = escapeshellarg($tempInput);
        $argOut = escapeshellarg($tempOutput);
        $cmd = "php {$this->binPath} compress {$argIn} --out={$argOut}";
        exec($cmd, $output, $exitCode);

        $this->assertSame(0, $exitCode);
        $this->assertTrue(file_exists($tempOutput), "Output file should be created");

        $content = file_get_contents($tempOutput);
        $this->assertSame("احمد ذهب الى المدرسة الجميلة!", trim($content));

        // Clean up
        @unlink($tempInput);
        @unlink($tempOutput);
    }
}
