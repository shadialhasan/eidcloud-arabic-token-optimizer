<?php

declare(strict_types=1);

namespace EidCloud\ArabicTokenOptimizer\Engine;

/**
 * Result data transfer object holding optimized text and performance metrics.
 */
class OptimizationResult implements \Stringable
{
    public readonly int $rawBytes;
    public readonly int $optimizedBytes;
    public readonly int $bytesSaved;
    public readonly float $bytesSavingsPercentage;

    public readonly int $rawChars;
    public readonly int $optimizedChars;
    public readonly int $charsSaved;
    public readonly float $charsSavingsPercentage;

    public readonly int $tokensSaved;
    public readonly float $tokensSavingsPercentage;
    public readonly float $estimatedCostSavingsUsd;

    /**
     * @param array<string> $appliedTransformations
     */
    public function __construct(
        public readonly string $rawText,
        public readonly string $optimizedText,
        public readonly int $rawTokens,
        public readonly int $optimizedTokens,
        public readonly array $appliedTransformations,
        public readonly float $executionTimeMs,
        public readonly OptimizationOptions $options
    ) {
        $this->rawBytes = strlen($this->rawText);
        $this->optimizedBytes = strlen($this->optimizedText);
        $this->bytesSaved = max(0, $this->rawBytes - $this->optimizedBytes);
        $this->bytesSavingsPercentage = $this->rawBytes > 0
            ? round(($this->bytesSaved / $this->rawBytes) * 100, 2)
            : 0.0;

        $this->rawChars = mb_strlen($this->rawText, 'UTF-8');
        $this->optimizedChars = mb_strlen($this->optimizedText, 'UTF-8');
        $this->charsSaved = max(0, $this->rawChars - $this->optimizedChars);
        $this->charsSavingsPercentage = $this->rawChars > 0
            ? round(($this->charsSaved / $this->rawChars) * 100, 2)
            : 0.0;

        $this->tokensSaved = max(0, $this->rawTokens - $this->optimizedTokens);
        $this->tokensSavingsPercentage = $this->rawTokens > 0
            ? round(($this->tokensSaved / $this->rawTokens) * 100, 2)
            : 0.0;

        // Cost savings for this specific payload: (saved tokens / 1,000,000) * costPerMillionTokensUsd
        $this->estimatedCostSavingsUsd = round(
            ($this->tokensSaved / 1_000_000) * $this->options->costPerMillionTokensUsd,
            6
        );
    }

    /**
     * Export all metrics to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'metrics' => [
                'tokens' => [
                    'raw' => $this->rawTokens,
                    'optimized' => $this->optimizedTokens,
                    'saved' => $this->tokensSaved,
                    'savings_percentage' => $this->tokensSavingsPercentage,
                ],
                'characters' => [
                    'raw' => $this->rawChars,
                    'optimized' => $this->optimizedChars,
                    'saved' => $this->charsSaved,
                    'savings_percentage' => $this->charsSavingsPercentage,
                ],
                'bytes' => [
                    'raw' => $this->rawBytes,
                    'optimized' => $this->optimizedBytes,
                    'saved' => $this->bytesSaved,
                    'savings_percentage' => $this->bytesSavingsPercentage,
                ],
                'financial' => [
                    'target_model' => $this->options->targetModel,
                    'cost_rate_per_million_usd' => $this->options->costPerMillionTokensUsd,
                    'estimated_savings_this_payload_usd' => $this->estimatedCostSavingsUsd,
                    'projected_savings_per_million_requests_usd' => round(
                        ($this->tokensSaved * $this->options->costPerMillionTokensUsd),
                        2
                    ),
                ],
                'performance' => [
                    'execution_time_ms' => round($this->executionTimeMs, 3),
                    'applied_transformations' => $this->appliedTransformations,
                ],
            ],
            'output' => [
                'optimized_text' => $this->optimizedText,
                'raw_text' => $this->rawText,
            ],
        ];
    }

    /**
     * Export result as JSON.
     */
    public function toJson(int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE): string
    {
        return (string) json_encode($this->toArray(), $flags);
    }

    public function __toString(): string
    {
        return $this->optimizedText;
    }
}
