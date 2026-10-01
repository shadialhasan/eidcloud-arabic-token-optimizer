<?php

declare(strict_types=1);

namespace EidCloud\ArabicTokenOptimizer\Normalizer;

use EidCloud\ArabicTokenOptimizer\Engine\OptimizationOptions;

/**
 * Strips Arabic Tashkeel (diacritics / vocalization).
 * In BPE tokenizers, diacritics cause catastrophic token fragmentation,
 * often multiplying token counts by 2x to 4x per word.
 */
class TashkeelNormalizer implements NormalizerInterface
{
    /**
     * Matches Unicode ranges:
     * - U+064B to U+065F (Fathatan, Dammatan, Kasratan, Fatha, Damma, Kasra, Shadda, Sukun, Quranic marks)
     * - U+0670 (Superscript dagger alef)
     */
    private const TASHKEEL_PATTERN = '/[\x{064B}-\x{065F}\x{0670}]/u';

    public function normalize(string $text, OptimizationOptions $options): string
    {
        if (!$this->isEnabled($options)) {
            return $text;
        }

        return (string) preg_replace(self::TASHKEEL_PATTERN, '', $text);
    }

    public function isEnabled(OptimizationOptions $options): bool
    {
        return $options->stripTashkeel;
    }

    public function getName(): string
    {
        return 'strip_tashkeel';
    }
}
