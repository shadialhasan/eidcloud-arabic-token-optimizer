<?php

declare(strict_types=1);

namespace EidCloud\ArabicTokenOptimizer\Tokenizer;

/**
 * Supported LLM Tokenizer Profiles with their respective cost metrics.
 */
enum TokenizerModel: string
{
    case GPT_4O = 'gpt-4o';
    case GPT_4 = 'gpt-4';
    case GPT_35_TURBO = 'gpt-3.5-turbo';
    case LLAMA_3 = 'llama3';
    case CLAUDE_35 = 'claude';
    case GENERIC_BPE = 'generic';

    /**
     * Approximate cost per million input tokens in USD (as of 2026).
     */
    public function getCostPerMillionUsd(): float
    {
        return match ($this) {
            self::GPT_4O => 2.50,
            self::GPT_4 => 5.00,
            self::GPT_35_TURBO => 0.50,
            self::LLAMA_3 => 0.80,
            self::CLAUDE_35 => 3.00,
            self::GENERIC_BPE => 2.00,
        };
    }

    /**
     * Vocabulary efficiency factor for Arabic base words.
     * Lower means higher compression efficiency in that tokenizer's merge table.
     */
    public function getArabicEfficiencyFactor(): float
    {
        return match ($this) {
            self::GPT_4O => 0.88,   // o200k_base has enlarged multilingual vocab
            self::LLAMA_3 => 0.90,  // Llama 3 128k vocab has expanded multilingual tokens
            self::CLAUDE_35 => 0.95,
            self::GPT_4 => 1.05,    // cl100k_base splits Arabic words more often
            self::GPT_35_TURBO => 1.05,
            self::GENERIC_BPE => 1.00,
        };
    }

    public static function fromString(string $name): self
    {
        $normalized = strtolower(trim($name));

        return match ($normalized) {
            'gpt-4o', 'o200k', 'gpt4o' => self::GPT_4O,
            'gpt-4', 'cl100k', 'gpt4' => self::GPT_4,
            'gpt-3.5-turbo', 'gpt-3.5', 'gpt35' => self::GPT_35_TURBO,
            'llama3', 'llama-3', 'llama', 'meta-llama' => self::LLAMA_3,
            'claude', 'claude-3', 'claude-3.5', 'anthropic' => self::CLAUDE_35,
            default => self::GENERIC_BPE,
        };
    }
}
