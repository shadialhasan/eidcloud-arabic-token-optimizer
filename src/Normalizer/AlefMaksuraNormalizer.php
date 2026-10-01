<?php

declare(strict_types=1);

namespace EidCloud\ArabicTokenOptimizer\Normalizer;

use EidCloud\ArabicTokenOptimizer\Engine\OptimizationOptions;

/**
 * Normalizes Alef Maksura to Yeh (ى -> ي).
 * Reduces vocabulary variations for search indexing, embeddings, and aggressive token reduction.
 */
class AlefMaksuraNormalizer implements NormalizerInterface
{
    public function normalize(string $text, OptimizationOptions $options): string
    {
        if (!$this->isEnabled($options)) {
            return $text;
        }

        return str_replace('ى', 'ي', $text);
    }

    public function isEnabled(OptimizationOptions $options): bool
    {
        return $options->normalizeAlefMaksura;
    }

    public function getName(): string
    {
        return 'normalize_alef_maksura';
    }
}
