<?php

declare(strict_types=1);

namespace EidCloud\ArabicTokenOptimizer\Tests;

use EidCloud\ArabicTokenOptimizer\Tokenizer\TokenEstimator;
use EidCloud\ArabicTokenOptimizer\Tokenizer\TokenizerModel;

class TokenEstimatorTest extends TestCase
{
    private TokenEstimator $estimator;

    public function setUp(): void
    {
        $this->estimator = new TokenEstimator();
    }

    public function testEmptyStringYieldsZeroTokens(): void
    {
        $this->assertSame(0, $this->estimator->estimate(""));
    }

    public function testCommonArabicWordsAreSingleTokens(): void
    {
        $words = ['في', 'من', 'إلى', 'على', 'عن', 'مع', 'هذا', 'هذه', 'كان', 'ما'];
        foreach ($words as $word) {
            $tokens = $this->estimator->estimate($word, TokenizerModel::GPT_4O);
            $this->assertSame(1, $tokens, "Expected word '{$word}' to estimate as 1 token");
        }
    }

    public function testTashkeelSignificantlyIncreasesTokens(): void
    {
        $cleanWord = "محمد";
        $vocalizedWord = "مُحَمَّدٌ";

        $cleanTokens = $this->estimator->estimate($cleanWord, TokenizerModel::GPT_4O);
        $vocalizedTokens = $this->estimator->estimate($vocalizedWord, TokenizerModel::GPT_4O);

        $this->assertGreaterThan($cleanTokens, $vocalizedTokens);
    }

    public function testTatweelSignificantlyIncreasesTokens(): void
    {
        $cleanWord = "مرحبا";
        $tatweelWord = "مـــــرحـــــبـــــا";

        $cleanTokens = $this->estimator->estimate($cleanWord, TokenizerModel::GPT_4O);
        $tatweelTokens = $this->estimator->estimate($tatweelWord, TokenizerModel::GPT_4O);

        $this->assertGreaterThan($cleanTokens, $tatweelTokens);
    }

    public function testEstimateSavingsMethod(): void
    {
        $raw = "مــــرحــــبــــاً بــــكــــمْ فِــــي عَــــالَــــمِ الــــذَّكَــــاءِ";
        $opt = "مرحبا بكم في عالم الذكاء";

        $savings = $this->estimator->estimateSavings($raw, $opt, TokenizerModel::GPT_4O);

        $this->assertGreaterThan(0, $savings['tokens_saved']);
        $this->assertGreaterThan(25.0, $savings['savings_percentage']);
        $this->assertSame('gpt-4o', $savings['model']);
        $this->assertGreaterThan(0.0, $savings['projected_savings_per_million_requests_usd']);
    }
}
