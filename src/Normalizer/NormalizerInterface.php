<?php

declare(strict_types=1);

namespace EidCloud\ArabicTokenOptimizer\Normalizer;

use EidCloud\ArabicTokenOptimizer\Engine\OptimizationOptions;

/**
 * Interface for Arabic text normalizers.
 */
interface NormalizerInterface
{
    /**
     * Normalize the given text according to the specific normalizer rules.
     */
    public function normalize(string $text, OptimizationOptions $options): string;

    /**
     * Check if this normalizer should be executed based on options.
     */
    public function isEnabled(OptimizationOptions $options): bool;

    /**
     * Unique identifier for the normalizer.
     */
    public function getName(): string;
}
