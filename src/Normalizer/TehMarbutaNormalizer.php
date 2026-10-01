<?php

declare(strict_types=1);

namespace EidCloud\ArabicTokenOptimizer\Normalizer;

use EidCloud\ArabicTokenOptimizer\Engine\OptimizationOptions;

/**
 * Normalizes Teh Marbuta to Heh (ة -> ه).
 * Reduces vocabulary variations for search indexing, embeddings, and aggressive token reduction.
 */
class TehMarbutaNormalizer implements NormalizerInterface
{
    public function normalize(string $text, OptimizationOptions $options): string
    {
        if (!$this->isEnabled($options)) {
            return $text;
        }

        return str_replace('ة', 'ه', $text);
    }

    public function isEnabled(OptimizationOptions $options): bool
    {
        return $options->normalizeTehMarbuta;
    }

    public function getName(): string
    {
        return 'normalize_teh_marbuta';
    }
}
