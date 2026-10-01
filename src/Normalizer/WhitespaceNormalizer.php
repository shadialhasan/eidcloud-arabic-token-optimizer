<?php

declare(strict_types=1);

namespace EidCloud\ArabicTokenOptimizer\Normalizer;

use EidCloud\ArabicTokenOptimizer\Engine\OptimizationOptions;

/**
 * Normalizes redundant whitespace, irregular Unicode spaces, and accidental formatting tokens.
 */
class WhitespaceNormalizer implements NormalizerInterface
{
    public function normalize(string $text, OptimizationOptions $options): string
    {
        if (!$this->isEnabled($options)) {
            return $text;
        }

        // Remove Byte Order Mark (BOM)
        $text = (string) preg_replace('/^\x{FEFF}/u', '', $text);

        // Normalize various Unicode spaces (non-breaking space, thin space, zero-width space, etc.) to standard space
        $text = (string) preg_replace('/[\x{00A0}\x{2000}-\x{200B}\x{202F}\x{205F}\x{3000}]/u', ' ', $text);

        // Standardize line endings to \n
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // Collapse multiple horizontal spaces/tabs into a single space
        $text = (string) preg_replace('/[ \t]+/u', ' ', $text);

        // Remove accidental spaces before punctuation marks common in RTL editors (e.g. "نص ، نص" -> "نص، نص")
        $text = (string) preg_replace('/ +([،؛؟!?:.,])/u', '$1', $text);

        // Trim whitespace at the end and beginning of each line
        $lines = explode("\n", $text);
        $trimmedLines = array_map(static fn(string $line): string => trim($line), $lines);
        $text = implode("\n", $trimmedLines);

        // Collapse 3 or more consecutive newlines into 2 (preserving standard paragraph breaks)
        $text = (string) preg_replace('/\n{3,}/u', "\n\n", $text);

        return trim($text);
    }

    public function isEnabled(OptimizationOptions $options): bool
    {
        return $options->normalizeWhitespace;
    }

    public function getName(): string
    {
        return 'normalize_whitespace';
    }
}
