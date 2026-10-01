<?php

declare(strict_types=1);

namespace EidCloud\ArabicTokenOptimizer\Normalizer;

use EidCloud\ArabicTokenOptimizer\Engine\OptimizationOptions;

/**
 * Strips Arabic Tatweel / Kashida (ـ - U+0640).
 * Tatweel is purely decorative in modern Arabic and wastes significant BPE tokens.
 */
class TatweelNormalizer implements NormalizerInterface
{
    private const TATWEEL_PATTERN = '/\x{0640}+/u';

    public function normalize(string $text, OptimizationOptions $options): string
    {
        if (!$this->isEnabled($options)) {
            return $text;
        }

        return (string) preg_replace(self::TATWEEL_PATTERN, '', $text);
    }

    public function isEnabled(OptimizationOptions $options): bool
    {
        return $options->stripTatweel;
    }

    public function getName(): string
    {
        return 'strip_tatweel';
    }
}
