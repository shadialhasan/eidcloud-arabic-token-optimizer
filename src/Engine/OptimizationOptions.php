<?php

declare(strict_types=1);

namespace EidCloud\ArabicTokenOptimizer\Engine;

/**
 * Configuration options for the Arabic Token Optimizer engine.
 */
class OptimizationOptions
{
    public function __construct(
        public readonly bool $stripTatweel = true,
        public readonly bool $normalizeWhitespace = true,
        public readonly bool $normalizeHamza = true,
        public readonly bool $normalizeTehMarbuta = false,
        public readonly bool $normalizeAlefMaksura = false,
        public readonly bool $stripRepeatingPunctuation = true,
        public readonly bool $stripTashkeel = true,
        public readonly bool $preserveMarkdownCode = true,
        public readonly string $targetModel = 'gpt-4o',
        public readonly float $costPerMillionTokensUsd = 2.50
    ) {
    }

    /**
     * Create default balanced options.
     */
    public static function balanced(): self
    {
        return new self(
            stripTatweel: true,
            normalizeWhitespace: true,
            normalizeHamza: true,
            normalizeTehMarbuta: false,
            normalizeAlefMaksura: false,
            stripRepeatingPunctuation: true,
            stripTashkeel: true,
            preserveMarkdownCode: true
        );
    }

    /**
     * Create conservative options (preserves Tashkeel and Hamzas).
     */
    public static function conservative(): self
    {
        return new self(
            stripTatweel: true,
            normalizeWhitespace: true,
            normalizeHamza: false,
            normalizeTehMarbuta: false,
            normalizeAlefMaksura: false,
            stripRepeatingPunctuation: true,
            stripTashkeel: false,
            preserveMarkdownCode: true
        );
    }

    /**
     * Create aggressive options (maximum token reduction).
     */
    public static function aggressive(): self
    {
        return new self(
            stripTatweel: true,
            normalizeWhitespace: true,
            normalizeHamza: true,
            normalizeTehMarbuta: true,
            normalizeAlefMaksura: true,
            stripRepeatingPunctuation: true,
            stripTashkeel: true,
            preserveMarkdownCode: true
        );
    }

    /**
     * Create options from an OptimizationLevel enum or string.
     */
    public static function fromLevel(OptimizationLevel|string $level): self
    {
        $resolvedLevel = is_string($level) ? OptimizationLevel::from(strtolower($level)) : $level;

        return match ($resolvedLevel) {
            OptimizationLevel::Conservative => self::conservative(),
            OptimizationLevel::Balanced => self::balanced(),
            OptimizationLevel::Aggressive => self::aggressive(),
        };
    }

    public function withStripTatweel(bool $strip): self
    {
        return new self(
            stripTatweel: $strip,
            normalizeWhitespace: $this->normalizeWhitespace,
            normalizeHamza: $this->normalizeHamza,
            normalizeTehMarbuta: $this->normalizeTehMarbuta,
            normalizeAlefMaksura: $this->normalizeAlefMaksura,
            stripRepeatingPunctuation: $this->stripRepeatingPunctuation,
            stripTashkeel: $this->stripTashkeel,
            preserveMarkdownCode: $this->preserveMarkdownCode,
            targetModel: $this->targetModel,
            costPerMillionTokensUsd: $this->costPerMillionTokensUsd
        );
    }

    public function withNormalizeWhitespace(bool $normalize): self
    {
        return new self(
            stripTatweel: $this->stripTatweel,
            normalizeWhitespace: $normalize,
            normalizeHamza: $this->normalizeHamza,
            normalizeTehMarbuta: $this->normalizeTehMarbuta,
            normalizeAlefMaksura: $this->normalizeAlefMaksura,
            stripRepeatingPunctuation: $this->stripRepeatingPunctuation,
            stripTashkeel: $this->stripTashkeel,
            preserveMarkdownCode: $this->preserveMarkdownCode,
            targetModel: $this->targetModel,
            costPerMillionTokensUsd: $this->costPerMillionTokensUsd
        );
    }

    public function withNormalizeHamza(bool $normalize): self
    {
        return new self(
            stripTatweel: $this->stripTatweel,
            normalizeWhitespace: $this->normalizeWhitespace,
            normalizeHamza: $normalize,
            normalizeTehMarbuta: $this->normalizeTehMarbuta,
            normalizeAlefMaksura: $this->normalizeAlefMaksura,
            stripRepeatingPunctuation: $this->stripRepeatingPunctuation,
            stripTashkeel: $this->stripTashkeel,
            preserveMarkdownCode: $this->preserveMarkdownCode,
            targetModel: $this->targetModel,
            costPerMillionTokensUsd: $this->costPerMillionTokensUsd
        );
    }

    public function withNormalizeTehMarbuta(bool $normalize): self
    {
        return new self(
            stripTatweel: $this->stripTatweel,
            normalizeWhitespace: $this->normalizeWhitespace,
            normalizeHamza: $this->normalizeHamza,
            normalizeTehMarbuta: $normalize,
            normalizeAlefMaksura: $this->normalizeAlefMaksura,
            stripRepeatingPunctuation: $this->stripRepeatingPunctuation,
            stripTashkeel: $this->stripTashkeel,
            preserveMarkdownCode: $this->preserveMarkdownCode,
            targetModel: $this->targetModel,
            costPerMillionTokensUsd: $this->costPerMillionTokensUsd
        );
    }

    public function withNormalizeAlefMaksura(bool $normalize): self
    {
        return new self(
            stripTatweel: $this->stripTatweel,
            normalizeWhitespace: $this->normalizeWhitespace,
            normalizeHamza: $this->normalizeHamza,
            normalizeTehMarbuta: $this->normalizeTehMarbuta,
            normalizeAlefMaksura: $normalize,
            stripRepeatingPunctuation: $this->stripRepeatingPunctuation,
            stripTashkeel: $this->stripTashkeel,
            preserveMarkdownCode: $this->preserveMarkdownCode,
            targetModel: $this->targetModel,
            costPerMillionTokensUsd: $this->costPerMillionTokensUsd
        );
    }

    public function withStripRepeatingPunctuation(bool $strip): self
    {
        return new self(
            stripTatweel: $this->stripTatweel,
            normalizeWhitespace: $this->normalizeWhitespace,
            normalizeHamza: $this->normalizeHamza,
            normalizeTehMarbuta: $this->normalizeTehMarbuta,
            normalizeAlefMaksura: $this->normalizeAlefMaksura,
            stripRepeatingPunctuation: $strip,
            stripTashkeel: $this->stripTashkeel,
            preserveMarkdownCode: $this->preserveMarkdownCode,
            targetModel: $this->targetModel,
            costPerMillionTokensUsd: $this->costPerMillionTokensUsd
        );
    }

    public function withStripTashkeel(bool $strip): self
    {
        return new self(
            stripTatweel: $this->stripTatweel,
            normalizeWhitespace: $this->normalizeWhitespace,
            normalizeHamza: $this->normalizeHamza,
            normalizeTehMarbuta: $this->normalizeTehMarbuta,
            normalizeAlefMaksura: $this->normalizeAlefMaksura,
            stripRepeatingPunctuation: $this->stripRepeatingPunctuation,
            stripTashkeel: $strip,
            preserveMarkdownCode: $this->preserveMarkdownCode,
            targetModel: $this->targetModel,
            costPerMillionTokensUsd: $this->costPerMillionTokensUsd
        );
    }

    public function withPreserveMarkdownCode(bool $preserve): self
    {
        return new self(
            stripTatweel: $this->stripTatweel,
            normalizeWhitespace: $this->normalizeWhitespace,
            normalizeHamza: $this->normalizeHamza,
            normalizeTehMarbuta: $this->normalizeTehMarbuta,
            normalizeAlefMaksura: $this->normalizeAlefMaksura,
            stripRepeatingPunctuation: $this->stripRepeatingPunctuation,
            stripTashkeel: $this->stripTashkeel,
            preserveMarkdownCode: $preserve,
            targetModel: $this->targetModel,
            costPerMillionTokensUsd: $this->costPerMillionTokensUsd
        );
    }

    public function withTargetModel(string $model, ?float $costPerMillion = null): self
    {
        return new self(
            stripTatweel: $this->stripTatweel,
            normalizeWhitespace: $this->normalizeWhitespace,
            normalizeHamza: $this->normalizeHamza,
            normalizeTehMarbuta: $this->normalizeTehMarbuta,
            normalizeAlefMaksura: $this->normalizeAlefMaksura,
            stripRepeatingPunctuation: $this->stripRepeatingPunctuation,
            stripTashkeel: $this->stripTashkeel,
            preserveMarkdownCode: $this->preserveMarkdownCode,
            targetModel: $model,
            costPerMillionTokensUsd: $costPerMillion ?? $this->costPerMillionTokensUsd
        );
    }
}
