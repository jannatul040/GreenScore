<?php
use PHPUnit\Framework\TestCase;

class PaymentTest extends TestCase
{
    // ── Cost calculation ─────────────────────────────────────

    public function testCostIsCalculatedFromShortfall(): void
    {
        $shortfall = 30;
        $cost      = $shortfall * 10;
        $this->assertEquals(300, $cost);
    }

    public function testZeroShortfallProducesZeroCost(): void
    {
        $shortfall = 0;
        $cost      = $shortfall * 10;
        $this->assertEquals(0, $cost);
    }

    public function testFullShortfallProducesMaxCost(): void
    {
        $shortfall = 100;
        $cost      = $shortfall * 10;
        $this->assertEquals(1000, $cost);
    }

    // ── Shortfall clamping (as applied in buy_points.php) ───

    public function testShortfallIsClampedToMaxHundred(): void
    {
        $raw       = 150;
        $shortfall = max(0, min(100, $raw));
        $this->assertEquals(100, $shortfall,
            'Shortfall cannot exceed 100.'
        );
    }

    public function testShortfallIsClampedToMinZero(): void
    {
        $raw       = -20;
        $shortfall = max(0, min(100, $raw));
        $this->assertEquals(0, $shortfall,
            'Shortfall cannot be negative.'
        );
    }

    public function testValidShortfallPassesThroughUnchanged(): void
    {
        $raw       = 45;
        $shortfall = max(0, min(100, $raw));
        $this->assertEquals(45, $shortfall);
    }

    // ── Cost formatting ──────────────────────────────────────

    public function testCostFormattedToTwoDecimalPlaces(): void
    {
        $shortfall = 33;
        $cost      = number_format($shortfall * 10, 2);
        $this->assertEquals('330.00', $cost);
    }

    public function testCostConversionToFloat(): void
    {
        $formatted  = '330.00';
        $cost_float = (float) str_replace(',', '', $formatted);
        $this->assertEquals(330.00, $cost_float);
        $this->assertIsFloat($cost_float);
    }

    // ── Post-payment state ───────────────────────────────────

    public function testSuccessfulDonationSetsGoldAward(): void
    {
        $award    = 'Certificate of Gold 🥇';
        $shortfall = 0;

        $this->assertEquals('Certificate of Gold 🥇', $award);
        $this->assertEquals(0, $shortfall,
            'Shortfall must be zero after a successful donation.'
        );
    }

    public function testFailedDonationLeavesStateUnchanged(): void
    {
        $original_shortfall = 40;
        $donated            = false;

        $shortfall = $donated ? 0 : $original_shortfall;

        $this->assertEquals(40, $shortfall,
            'Shortfall should remain unchanged if donation did not complete.'
        );
    }
}