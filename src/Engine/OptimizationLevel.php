<?php

declare(strict_types=1);

namespace EidCloud\ArabicTokenOptimizer\Engine;

/**
 * Predefined optimization levels.
 */
enum OptimizationLevel: string
{
    /**
     * Conservative: Safe for formal and legal texts.
     * Strips Tatweel, cleans whitespace, collapses repeating punctuation.
     * Preserves diacritics (Tashkeel), Hamzas, Teh Marbuta, and Alef Maksura.
     */
    case Conservative = 'conservative';

    /**
     * Balanced (Default & Recommended):
     * Maximum compression while preserving full semantic clarity for LLMs.
     * Strips Tatweel, normalizes whitespace, strips Tashkeel, normalizes erratic Hamzas,
     * collapses repeating punctuation. Preserves Teh Marbuta (ة) and Alef Maksura (ى).
     */
    case Balanced = 'balanced';

    /**
     * Aggressive:
     * Maximum token savings. In addition to Balanced, normalizes Teh Marbuta (ة -> ه)
     * and Alef Maksura (ى -> ي). Suitable for search indexing, embeddings, and chat prompts.
     */
    case Aggressive = 'aggressive';
}
