<?php

declare(strict_types=1);

namespace EidCloud\ArabicTokenOptimizer\Tests;

use EidCloud\ArabicTokenOptimizer\ArabicTokenOptimizer;
use EidCloud\ArabicTokenOptimizer\Engine\OptimizationLevel;
use EidCloud\ArabicTokenOptimizer\Engine\OptimizationOptions;
use EidCloud\ArabicTokenOptimizer\Engine\Optimizer;

class OptimizerTest extends TestCase
{
    private Optimizer $optimizer;

    public function setUp(): void
    {
        $this->optimizer = new Optimizer();
    }

    public function testTatweelRemoval(): void
    {
        $input = "مــــرحــــبــــاً بــــكــــم";
        $result = $this->optimizer->optimize($input);

        $this->assertStringNotContainsString('ـ', $result->optimizedText);
        $this->assertSame("مرحبا بكم", $result->optimizedText);
        $this->assertGreaterThan(0, $result->tokensSaved);
    }

    public function testWhitespaceNormalization(): void
    {
        $input = "  نص   تجريبي    مع   مسافات    كثيرة   \t\t\n\n\n\nوسطر   جديد   ";
        $result = $this->optimizer->optimize($input);

        $this->assertSame("نص تجريبي مع مسافات كثيرة\n\nوسطر جديد", $result->optimizedText);
        $this->assertLessThan(strlen($input), strlen($result->optimizedText));
    }

    public function testRtlPunctuationSpaceNormalization(): void
    {
        $input = "الذكاء الاصطناعي ، تقنية حديثة ؛ هل توافق ؟";
        $result = $this->optimizer->optimize($input);

        $this->assertSame("الذكاء الاصطناعي، تقنية حديثة؛ هل توافق؟", $result->optimizedText);
    }

    public function testHamzaNormalization(): void
    {
        $input = "أحمد ذهب إلى المنزل آكلاً ٱلتفاح";
        $result = $this->optimizer->optimize($input);

        $this->assertSame("احمد ذهب الى المنزل اكلا التفاح", $result->optimizedText);
    }

    public function testTehMarbutaNormalizationOptions(): void
    {
        $input = "مدرسة جميلة";

        // Default balanced options preserve Teh Marbuta
        $balanced = $this->optimizer->optimize($input, OptimizationOptions::balanced());
        $this->assertSame("مدرسة جميلة", $balanced->optimizedText);

        // Aggressive options normalize Teh Marbuta to Heh
        $aggressive = $this->optimizer->optimize($input, OptimizationOptions::aggressive());
        $this->assertSame("مدرسه جميله", $aggressive->optimizedText);
    }

    public function testAlefMaksuraNormalizationOptions(): void
    {
        $input = "مستشفى ومبنى وسعى";

        // Balanced preserves Alef Maksura
        $balanced = $this->optimizer->optimize($input, OptimizationOptions::balanced());
        $this->assertSame("مستشفى ومبنى وسعى", $balanced->optimizedText);

        // Aggressive converts Alef Maksura to Yeh
        $aggressive = $this->optimizer->optimize($input, OptimizationOptions::aggressive());
        $this->assertSame("مستشفي ومبني وسعي", $aggressive->optimizedText);
    }

    public function testRepeatingPunctuationStripping(): void
    {
        $input = "هل هذا حقيقي؟؟؟؟؟ نعم بالطبع!!!!! رائع..... وممتاز،،،";
        $result = $this->optimizer->optimize($input);

        $this->assertSame("هل هذا حقيقي؟ نعم بالطبع! رائع... وممتاز،", $result->optimizedText);
    }

    public function testTashkeelStrippingAndPreservation(): void
    {
        $input = "الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ";

        // Stripped by default (balanced)
        $stripped = $this->optimizer->optimize($input, OptimizationOptions::balanced());
        $this->assertSame("الحمد لله رب العالمين", $stripped->optimizedText);
        $this->assertGreaterThan(5, $stripped->tokensSaved);

        // Preserved in conservative
        $preserved = $this->optimizer->optimize($input, OptimizationOptions::conservative());
        $this->assertSame("الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ", $preserved->optimizedText);
    }

    public function testPreserveMarkdownCodeBlocks(): void
    {
        $input = "شرح الكود البرمجي:\n```php\n// تـــــعــــلـــــيــــق\n\$msg = \"مـــــرحـــــبـــــاً\";\n```\nنـــــص خـــــارج الـــــكـــــود";
        $result = $this->optimizer->optimize($input, OptimizationOptions::balanced());

        // Code block internal tatweel must be strictly preserved
        $this->assertStringContainsString('// تـــــعــــلـــــيــــق', $result->optimizedText);
        $this->assertStringContainsString('$msg = "مـــــرحـــــبـــــاً";', $result->optimizedText);

        // Outside code block must be optimized
        $this->assertStringContainsString('نص خارج الكود', $result->optimizedText);
    }

    public function testMixedArabicEnglishAndEmoji(): void
    {
        $input = "نموذج GPT-4o يدعم اللّغة العربية 🚀 بنسبة 100% !!!!!!!";
        $result = $this->optimizer->optimize($input);

        $this->assertSame("نموذج GPT-4o يدعم اللغة العربية 🚀 بنسبة 100%!", $result->optimizedText);
        $this->assertStringContainsString("GPT-4o", $result->optimizedText);
        $this->assertStringContainsString("🚀", $result->optimizedText);
    }

    public function testEmptyAndWhitespaceInput(): void
    {
        $emptyResult = $this->optimizer->optimize("");
        $this->assertSame("", $emptyResult->optimizedText);
        $this->assertSame(0, $emptyResult->rawTokens);
        $this->assertSame(0, $emptyResult->optimizedTokens);

        $spaceResult = $this->optimizer->optimize("    \n\t  ");
        $this->assertSame("", $spaceResult->optimizedText);
    }

    public function testTokenReductionExceeds25PercentOnTypicalVocalizedText(): void
    {
        $input = "الْحَمْدُ لِلَّهِ الَّذِي هَدَانَا لِهَٰذَا وَمَا كُنَّا لِنَهْتَدِيَ لَوْلَا أَنْ هَدَانَا اللَّهُ ،،،، شُـــــكْـــــراً جَـــــزِيـــــلاً !!!!!!!";
        $result = $this->optimizer->optimize($input, OptimizationOptions::balanced());

        // Expect >= 25% token savings
        $this->assertGreaterThan(25.0, $result->tokensSavingsPercentage);
        $this->assertGreaterThan(0, $result->tokensSaved);
        $this->assertLessThan($result->rawTokens, $result->optimizedTokens);
    }

    public function testOptimizationResultSerialization(): void
    {
        $input = "مـــــرحـــــبـــــاً";
        $result = $this->optimizer->optimize($input);

        $array = $result->toArray();
        $this->assertArrayHasKey('metrics', $array);
        $this->assertArrayHasKey('tokens', $array['metrics']);
        $this->assertArrayHasKey('output', $array);

        $json = $result->toJson();
        $decoded = json_decode($json, true);
        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame("مرحبا", $decoded['output']['optimized_text']);
    }

    public function testFacadeHelpers(): void
    {
        $text = "مـــــرحـــــبـــــاً";
        $compressed = ArabicTokenOptimizer::compress($text);
        $this->assertSame("مرحبا", $compressed);

        $tokens = ArabicTokenOptimizer::estimateTokens("مرحبا بالعالم");
        $this->assertGreaterThan(0, $tokens);
    }

    private function assertArrayHasKey(string $key, array $array): void
    {
        $this->assertTrue(array_key_exists($key, $array), "Failed asserting that array has key '{$key}'.");
    }

    private function assertStringNotContainsString(string $needle, string $haystack): void
    {
        $this->assertFalse(str_contains($haystack, $needle), "Failed asserting that string does not contain '{$needle}'.");
    }
}
