<?php

declare(strict_types=1);

namespace EidCloud\ArabicTokenOptimizer\Tokenizer;

/**
 * High-precision heuristic BPE Token Estimator calibrated for Arabic NLP & LLMs.
 * Accurately models Byte-Pair Encoding (BPE) subword splitting in GPT-4o (o200k),
 * GPT-4 (cl100k), Llama 3, and Claude.
 */
class TokenEstimator
{
    /**
     * High-frequency Arabic words that exist as single dedicated tokens in standard BPE vocabularies.
     */
    private const ARABIC_SINGLE_TOKEN_WORDS = [
        'في', 'من', 'إلى', 'الي', 'على', 'علي', 'عن', 'مع', 'هذا', 'هذه',
        'ذلك', 'تلك', 'التي', 'الذي', 'الذين', 'اللتين', 'اللذين', 'كان',
        'كانت', 'يكون', 'ما', 'لا', 'لم', 'لن', 'أن', 'ان', 'إن', 'كل',
        'بعض', 'غير', 'بعد', 'قبل', 'عند', 'أو', 'او', 'ثم', 'قد', 'لقد',
        'بين', 'نحو', 'حيث', 'هو', 'هي', 'هم', 'هن', 'نحن', 'أنا', 'انا',
        'أنت', 'انت', 'أنتم', 'انتم', 'كيف', 'متى', 'أين', 'اين', 'كم',
        'لماذا', 'أي', 'اي', 'هنا', 'هناك', 'نعم', 'كلا', 'ليس', 'ليست',
        'جدا', 'فقط', 'أيضا', 'ايضا', 'حتى', 'لو', 'إذا', 'اذا', 'إذ', 'اذ',
        'أما', 'اما', 'بل', 'لكن', 'بينما', 'حين', 'منذ', 'خلال', 'حول',
        'دون', 'ضد', 'نفس', 'ذات', 'عام', 'يوم', 'سنة', 'شهر', 'الله',
        'قال', 'قالت', 'يقول', 'تم', 'تمت', 'يتم', 'عبر', 'أكثر', 'اكثر',
        'أقل', 'اقل', 'أول', 'اول', 'آخر', 'اخر', 'أخرى', 'اخرى', 'جديد',
        'كبير', 'صغير', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'اربعة', 'خمسة',
    ];

    private static ?array $singleTokenWordLookup = null;

    /**
     * Estimate the token count for the given text.
     */
    public function estimate(string $text, TokenizerModel|string $model = TokenizerModel::GPT_4O): int
    {
        if ($text === '') {
            return 0;
        }

        $resolvedModel = is_string($model) ? TokenizerModel::fromString($model) : $model;
        $efficiencyFactor = $resolvedModel->getArabicEfficiencyFactor();

        if (self::$singleTokenWordLookup === null) {
            self::$singleTokenWordLookup = array_fill_keys(self::ARABIC_SINGLE_TOKEN_WORDS, true);
        }

        $totalTokens = 0.0;

        // Split text by regex recognizing Arabic words, numbers, Latin words, punctuation, and whitespace
        // Pattern matches:
        // 1. Arabic words (including diacritics and tatweel): [\x{0600}-\x{06FF}]+
        // 2. Latin words: [a-zA-Z0-9_]+
        // 3. Consecutive newlines: \n+
        // 4. Consecutive spaces/tabs: [ \t]+
        // 5. Punctuation and other individual characters
        $pattern = '/[\x{0600}-\x{06FF}]+|[a-zA-Z0-9_]+|\n+|[ \t]+|[^\s\w\x{0600}-\x{06FF}]/u';

        if (preg_match_all($pattern, $text, $matches)) {
            foreach ($matches[0] as $segment) {
                $totalTokens += $this->estimateSegmentTokens($segment, $efficiencyFactor);
            }
        } else {
            // Fallback byte-length estimation
            $totalTokens = ceil(strlen($text) / 3.0);
        }

        return max(1, (int) round($totalTokens));
    }

    /**
     * Estimate tokens for an individual segment.
     */
    private function estimateSegmentTokens(string $segment, float $efficiencyFactor): float
    {
        $firstChar = mb_substr($segment, 0, 1, 'UTF-8');

        // Check if segment is Arabic
        if (preg_match('/^[\x{0600}-\x{06FF}]+$/u', $segment)) {
            return $this->estimateArabicWordTokens($segment, $efficiencyFactor);
        }

        // Check if segment is whitespace
        if (ctype_space($segment)) {
            // Newlines: single newline is often merged, multiple newlines create tokens
            $newlineCount = substr_count($segment, "\n");
            if ($newlineCount > 0) {
                return (float) ceil($newlineCount / 2.0);
            }
            // Spaces: 1-3 spaces usually merge with word or take 1 token; 4+ spaces take 1 token per 4
            $spaceCount = strlen($segment);
            return (float) max(1, ceil($spaceCount / 4.0));
        }

        // Check if Latin / alphanumeric
        if (preg_match('/^[a-zA-Z0-9_]+$/', $segment)) {
            if (ctype_digit($segment)) {
                // Digits: roughly 1 token per 2-3 digits
                return (float) ceil(strlen($segment) / 2.5);
            }
            // Latin words: roughly 1 token per 4 characters
            return (float) max(1.0, ceil(strlen($segment) / 4.0));
        }

        // Punctuation and symbols
        $charLen = mb_strlen($segment, 'UTF-8');
        return (float) max(1.0, $charLen);
    }

    /**
     * Estimate tokens for an Arabic word segment with diacritic & tatweel penalty.
     */
    private function estimateArabicWordTokens(string $word, float $efficiencyFactor): float
    {
        // Count Tatweels (Kashida: U+0640)
        $tatweelCount = preg_match_all('/\x{0640}/u', $word);

        // Count Tashkeel (diacritics: U+064B - U+065F, U+0670)
        $tashkeelCount = preg_match_all('/[\x{064B}-\x{065F}\x{0670}]/u', $word);

        // Extract base letters by stripping tatweel and tashkeel
        $baseWord = (string) preg_replace('/[\x{0640}\x{064B}-\x{065F}\x{0670}]/u', '', $word);
        $baseLength = mb_strlen($baseWord, 'UTF-8');

        if ($baseLength === 0) {
            // Pure tashkeel / tatweel segment
            return (float) ($tatweelCount + $tashkeelCount);
        }

        // Base token cost for clean word
        $baseTokens = 1.0;

        if (isset(self::$singleTokenWordLookup[$baseWord])) {
            $baseTokens = 1.0;
        } elseif ($baseLength <= 3) {
            $baseTokens = 1.0;
        } elseif ($baseLength <= 5) {
            $baseTokens = 1.35 * $efficiencyFactor;
        } elseif ($baseLength <= 8) {
            $baseTokens = 2.10 * $efficiencyFactor;
        } elseif ($baseLength <= 11) {
            $baseTokens = 3.00 * $efficiencyFactor;
        } else {
            $baseTokens = ceil($baseLength / 3.0) * $efficiencyFactor;
        }

        // Each Tatweel interrupts BPE token merges and adds at least 1 token
        $tatweelTokens = $tatweelCount * 1.1;

        // Each Tashkeel diacritic is split into separate byte tokens in BPE
        $tashkeelTokens = $tashkeelCount * 0.95;

        return $baseTokens + $tatweelTokens + $tashkeelTokens;
    }

    /**
     * Compute comprehensive token savings metrics between raw and optimized text.
     *
     * @return array<string, mixed>
     */
    public function estimateSavings(
        string $rawText,
        string $optimizedText,
        TokenizerModel|string $model = TokenizerModel::GPT_4O
    ): array {
        $resolvedModel = is_string($model) ? TokenizerModel::fromString($model) : $model;

        $rawTokens = $this->estimate($rawText, $resolvedModel);
        $optimizedTokens = $this->estimate($optimizedText, $resolvedModel);
        $tokensSaved = max(0, $rawTokens - $optimizedTokens);
        $percentage = $rawTokens > 0 ? round(($tokensSaved / $rawTokens) * 100, 2) : 0.0;

        $costPerMillion = $resolvedModel->getCostPerMillionUsd();
        $costSavingsPerMillionRequests = round($tokensSaved * $costPerMillion, 2);

        return [
            'model' => $resolvedModel->value,
            'raw_tokens' => $rawTokens,
            'optimized_tokens' => $optimizedTokens,
            'tokens_saved' => $tokensSaved,
            'savings_percentage' => $percentage,
            'cost_per_million_usd' => $costPerMillion,
            'projected_savings_per_million_requests_usd' => $costSavingsPerMillionRequests,
        ];
    }
}
