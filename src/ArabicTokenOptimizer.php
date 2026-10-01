<?php

declare(strict_types=1);

namespace EidCloud\ArabicTokenOptimizer;

use EidCloud\ArabicTokenOptimizer\Engine\OptimizationLevel;
use EidCloud\ArabicTokenOptimizer\Engine\OptimizationOptions;
use EidCloud\ArabicTokenOptimizer\Engine\OptimizationResult;
use EidCloud\ArabicTokenOptimizer\Engine\Optimizer;
use EidCloud\ArabicTokenOptimizer\Tokenizer\TokenEstimator;
use EidCloud\ArabicTokenOptimizer\Tokenizer\TokenizerModel;

/**
 * Primary Facade for EidCloud Arabic Token Optimizer.
 */
class ArabicTokenOptimizer
{
    public const VERSION = '1.0.0';

    private static ?Optimizer $defaultOptimizer = null;

    /**
     * Get or initialize the default Optimizer instance.
     */
    public static function getDefaultOptimizer(): Optimizer
    {
        if (self::$defaultOptimizer === null) {
            self::$defaultOptimizer = new Optimizer();
        }

        return self::$defaultOptimizer;
    }

    /**
     * Create a new Optimizer instance.
     */
    public static function create(): Optimizer
    {
        return new Optimizer();
    }

    /**
     * Optimize Arabic text and get complete result metrics.
     */
    public static function optimize(
        string $text,
        OptimizationOptions|OptimizationLevel|string|null $options = null
    ): OptimizationResult {
        $resolvedOptions = self::resolveOptions($options);
        return self::getDefaultOptimizer()->optimize($text, $resolvedOptions);
    }

    /**
     * Quick compression helper returning only the compressed text.
     */
    public static function compress(
        string $text,
        OptimizationOptions|OptimizationLevel|string|null $options = null
    ): string {
        $resolvedOptions = self::resolveOptions($options);
        return self::getDefaultOptimizer()->compress($text, $resolvedOptions);
    }

    /**
     * Estimate token count for a given text.
     */
    public static function estimateTokens(
        string $text,
        TokenizerModel|string $model = TokenizerModel::GPT_4O
    ): int {
        return self::getDefaultOptimizer()->getTokenEstimator()->estimate($text, $model);
    }

    /**
     * Resolve options argument to OptimizationOptions object.
     */
    private static function resolveOptions(
        OptimizationOptions|OptimizationLevel|string|null $options
    ): OptimizationOptions {
        if ($options === null) {
            return OptimizationOptions::balanced();
        }

        if ($options instanceof OptimizationOptions) {
            return $options;
        }

        return OptimizationOptions::fromLevel($options);
    }
}
