<?php
use PHPUnit\Framework\TestCase;

class GreenCalculatorTest extends TestCase
{
    // ── Score calculation ────────────────────────────────────

    public function testAllGreenGivesHundred(): void
    {
        $selections = array_fill(0, 10, 'GREEN');
        $this->assertEquals(100, $this->calculateScore($selections));
    }

    public function testAllAmberGivesFifty(): void
    {
        $selections = array_fill(0, 10, 'AMBER');
        $this->assertEquals(50, $this->calculateScore($selections));
    }

    public function testAllRedGivesZero(): void
    {
        $selections = array_fill(0, 10, 'RED');
        $this->assertEquals(0, $this->calculateScore($selections));
    }

    public function testMixedSelections(): void
    {
        // 5 GREEN (50) + 3 AMBER (15) + 2 RED (0) = 65
        $selections = ['GREEN','GREEN','GREEN','GREEN','GREEN','AMBER','AMBER','AMBER','RED','RED'];
        $this->assertEquals(65, $this->calculateScore($selections));
    }

    // ── Award thresholds ────────────────────────────────────

    public function testScoreEightyAwardsGold(): void
    {
        $this->assertEquals('Certificate of Gold 🥇', $this->getAward(80));
    }

    public function testScoreHundredAwardsGold(): void
    {
        $this->assertEquals('Certificate of Gold 🥇', $this->getAward(100));
    }

    public function testScoreSixtyFiveAwardsSilver(): void
    {
        $this->assertEquals('Certificate of Silver 🥈', $this->getAward(65));
    }

    public function testScoreSeventyNineAwardsSilver(): void
    {
        $this->assertEquals('Certificate of Silver 🥈', $this->getAward(79));
    }

    public function testScoreFiftyOneAwardsBronze(): void
    {
        $this->assertEquals('Certificate of Bronze 🥉', $this->getAward(51));
    }

    public function testScoreSixtyFourAwardsBronze(): void
    {
        $this->assertEquals('Certificate of Bronze 🥉', $this->getAward(64));
    }

    public function testScoreFiftyAwardsParticipation(): void
    {
        $this->assertEquals('Certificate of Participation 👏', $this->getAward(50));
    }

    public function testScoreZeroAwardsParticipation(): void
    {
        $this->assertEquals('Certificate of Participation 👏', $this->getAward(0));
    }

    // ── Shortfall and cost calculation ──────────────────────

    public function testShortfallCalculation(): void
    {
        $this->assertEquals(35, $this->getShortfall(65));
        $this->assertEquals(0,  $this->getShortfall(100));
        $this->assertEquals(100,$this->getShortfall(0));
    }

    public function testCostCalculation(): void
    {
        $this->assertEquals(350, $this->getCost(65));
        $this->assertEquals(0,   $this->getCost(100));
        $this->assertEquals(1000,$this->getCost(0));
    }

    public function testPerfectScoreHasZeroShortfallAndZeroCost(): void
    {
        $shortfall = $this->getShortfall(100);
        $cost      = $this->getCost(100);

        $this->assertEquals(0, $shortfall);
        $this->assertEquals(0, $cost);
    }

    // ── Helpers ──────────────────────────────────────────────

    private function calculateScore(array $selections): int
    {
        $score = 0;
        foreach ($selections as $choice) {
            if ($choice === 'GREEN')     $score += 10;
            elseif ($choice === 'AMBER') $score += 5;
        }
        return $score;
    }

    private function getAward(int $total): string
    {
        if ($total >= 80) return 'Certificate of Gold 🥇';
        if ($total >= 65) return 'Certificate of Silver 🥈';
        if ($total > 50)  return 'Certificate of Bronze 🥉';
        return 'Certificate of Participation 👏';
    }

    private function getShortfall(int $total): int
    {
        return 100 - $total;
    }

    private function getCost(int $total): int
    {
        return $this->getShortfall($total) * 10;
    }
}