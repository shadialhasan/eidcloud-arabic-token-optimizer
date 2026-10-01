<?php

declare(strict_types=1);

namespace EidCloud\ArabicTokenOptimizer\Normalizer;

use EidCloud\ArabicTokenOptimizer\Engine\OptimizationOptions;

/**
 * Normalizes redundant repeating punctuation marks (e.g. ؟؟؟ -> ؟, !!! -> !, .... -> ...).
 */
class PunctuationNormalizer implements NormalizerInterface
{
    public function normalize(string $text, OptimizationOptions $options): string
    {
        if (!$this->isEnabled($options)) {
            return $text;
        }

        // Collapse repeating Arabic question marks
        $text = (string) preg_replace('/[؟]{2,}/u', '؟', $text);

        // Collapse repeating Latin question marks
        $text = (string) preg_replace('/\?{2,}/u', '?', $text);

        // Collapse repeating exclamation marks
        $text = (string) preg_replace('/!{2,}/u', '!', $text);

        // Collapse mixed question/exclamation: ?!?! -> ?! or !؟!؟ -> !؟
        $text = (string) preg_replace('/([؟!?!]){2,}/u', '$1', $text);

        // Collapse repeating Arabic commas: ،،، -> ،
        $text = (string) preg_replace('/[،]{2,}/u', '،', $text);

        // Collapse repeating Latin commas: ,,, -> ,
        $text = (string) preg_replace('/,{2,}/u', ',', $text);

        // Collapse repeating colons / semicolons
        $text = (string) preg_replace('/:{2,}/u', ':', $text);
        $text = (string) preg_replace('/;{2,}/u', ';', $text);
        $text = (string) preg_replace('/[؛]{2,}/u', '؛', $text);

        // Collapse 4 or more periods/dots to ellipsis (3 dots)
        $text = (string) preg_replace('/\.{4,}/u', '...', $text);

        return $text;
    }

    public function isEnabled(OptimizationOptions $options): bool
    {
        return $options->stripRepeatingPunctuation;
    }

    public function getName(): string
    {
        return 'strip_repeating_punctuation';
    }
}
