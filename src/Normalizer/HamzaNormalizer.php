<?php

declare(strict_types=1);

namespace EidCloud\ArabicTokenOptimizer\Normalizer;

use EidCloud\ArabicTokenOptimizer\Engine\OptimizationOptions;

/**
 * Normalizes erratic Alef Hamzas to bare Alef (أ, إ, آ, ٱ -> ا).
 * Standardizes vocabulary forms and maximizes BPE subword matching.
 */
class HamzaNormalizer implements NormalizerInterface
{
    private const HAMZA_MAP = [
        'أ' => 'ا', // U+0623 -> U+0627
        'إ' => 'ا', // U+0625 -> U+0627
        'آ' => 'ا', // U+0622 -> U+0627
        'ٱ' => 'ا', // U+0671 -> U+0627
    ];

    public function normalize(string $text, OptimizationOptions $options): string
    {
        if (!$this->isEnabled($options)) {
            return $text;
        }

        return strtr($text, self::HAMZA_MAP);
    }

    public function isEnabled(OptimizationOptions $options): bool
    {
        return $options->normalizeHamza;
    }

    public function getName(): string
    {
        return 'normalize_hamza';
    }
}
