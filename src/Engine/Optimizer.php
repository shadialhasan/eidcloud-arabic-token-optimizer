<?php

declare(strict_types=1);

namespace EidCloud\ArabicTokenOptimizer\Engine;

use EidCloud\ArabicTokenOptimizer\Normalizer\AlefMaksuraNormalizer;
use EidCloud\ArabicTokenOptimizer\Normalizer\HamzaNormalizer;
use EidCloud\ArabicTokenOptimizer\Normalizer\NormalizerInterface;
use EidCloud\ArabicTokenOptimizer\Normalizer\PunctuationNormalizer;
use EidCloud\ArabicTokenOptimizer\Normalizer\TashkeelNormalizer;
use EidCloud\ArabicTokenOptimizer\Normalizer\TatweelNormalizer;
use EidCloud\ArabicTokenOptimizer\Normalizer\TehMarbutaNormalizer;
use EidCloud\ArabicTokenOptimizer\Normalizer\WhitespaceNormalizer;
use EidCloud\ArabicTokenOptimizer\Tokenizer\TokenEstimator;
use EidCloud\ArabicTokenOptimizer\Tokenizer\TokenizerModel;

/**
 * Core Optimization Engine for Arabic LLM text processing.
 */
class Optimizer
{
    /** @var array<NormalizerInterface> */
    private array $normalizers;

    private TokenEstimator $tokenEstimator;

    /**
     * @param array<NormalizerInterface>|null $normalizers
     */
    public function __construct(
        ?array $normalizers = null,
        ?TokenEstimator $tokenEstimator = null
    ) {
        $this->normalizers = $normalizers ?? [
            new TatweelNormalizer(),
            new TashkeelNormalizer(),
            new HamzaNormalizer(),
            new TehMarbutaNormalizer(),
            new AlefMaksuraNormalizer(),
            new PunctuationNormalizer(),
            new WhitespaceNormalizer(),
        ];

        $this->tokenEstimator = $tokenEstimator ?? new TokenEstimator();
    }

    /**
     * Optimize Arabic text and return comprehensive result with metrics.
     */
    public function optimize(string $text, ?OptimizationOptions $options = null): OptimizationResult
    {
        $options = $options ?? OptimizationOptions::balanced();
        $startTime = hrtime(true);

        if (trim($text) === '') {
            $elapsedMs = (hrtime(true) - $startTime) / 1e6;
            return new OptimizationResult(
                rawText: $text,
                optimizedText: '',
                rawTokens: 0,
                optimizedTokens: 0,
                appliedTransformations: [],
                executionTimeMs: $elapsedMs,
                options: $options
            );
        }

        $codePlaceholders = [];
        $workingText = $text;

        // Step 1: Protect markdown code blocks if configured
        if ($options->preserveMarkdownCode) {
            $workingText = $this->protectCodeBlocks($workingText, $codePlaceholders);
        }

        $applied = [];

        // Step 2: Run all active normalizers in sequential order
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer->isEnabled($options)) {
                $previousText = $workingText;
                $workingText = $normalizer->normalize($workingText, $options);
                if ($previousText !== $workingText) {
                    $applied[] = $normalizer->getName();
                }
            }
        }

        // Step 3: Restore protected code blocks
        if ($options->preserveMarkdownCode && !empty($codePlaceholders)) {
            $workingText = strtr($workingText, $codePlaceholders);
        }

        $elapsedMs = (hrtime(true) - $startTime) / 1e6;

        $targetModel = TokenizerModel::fromString($options->targetModel);
        $rawTokens = $this->tokenEstimator->estimate($text, $targetModel);
        $optimizedTokens = $this->tokenEstimator->estimate($workingText, $targetModel);

        return new OptimizationResult(
            rawText: $text,
            optimizedText: $workingText,
            rawTokens: $rawTokens,
            optimizedTokens: $optimizedTokens,
            appliedTransformations: $applied,
            executionTimeMs: $elapsedMs,
            options: $options
        );
    }

    /**
     * Quick compression helper returning just the optimized text.
     */
    public function compress(string $text, ?OptimizationOptions $options = null): string
    {
        return $this->optimize($text, $options)->optimizedText;
    }

    /**
     * Optimize content from a file and optionally write output to destination.
     */
    public function optimizeFile(
        string $inputFilePath,
        ?string $outputFilePath = null,
        ?OptimizationOptions $options = null
    ): OptimizationResult {
        if (!file_exists($inputFilePath) || !is_readable($inputFilePath)) {
            throw new \InvalidArgumentException(sprintf('Input file not found or not readable: "%s"', $inputFilePath));
        }

        $content = (string) file_get_contents($inputFilePath);
        $result = $this->optimize($content, $options);

        if ($outputFilePath !== null) {
            $dir = dirname($outputFilePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            file_put_contents($outputFilePath, $result->optimizedText);
        }

        return $result;
    }

    /**
     * Protects fenced code blocks (```...```) and inline code (`...`) from normalization.
     *
     * @param array<string, string> $placeholders
     */
    private function protectCodeBlocks(string $text, array &$placeholders): string
    {
        // Fenced code blocks
        $text = (string) preg_replace_callback(
            '/```[\s\S]*?```/u',
            function (array $match) use (&$placeholders): string {
                $token = '___EIDCLOUD_FENCE_CODE_' . count($placeholders) . '___';
                $placeholders[$token] = $match[0];
                return $token;
            },
            $text
        );

        // Inline code
        $text = (string) preg_replace_callback(
            '/`[^`\n]+`/u',
            function (array $match) use (&$placeholders): string {
                $token = '___EIDCLOUD_INLINE_CODE_' . count($placeholders) . '___';
                $placeholders[$token] = $match[0];
                return $token;
            },
            $text
        );

        return $text;
    }

    public function getTokenEstimator(): TokenEstimator
    {
        return $this->tokenEstimator;
    }

    /**
     * @return array<NormalizerInterface>
     */
    public function getNormalizers(): array
    {
        return $this->normalizers;
    }

    public function registerNormalizer(NormalizerInterface $normalizer): self
    {
        $this->normalizers[] = $normalizer;
        return $this;
    }
}
