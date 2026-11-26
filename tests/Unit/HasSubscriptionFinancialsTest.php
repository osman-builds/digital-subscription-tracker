<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Concerns\HasSubscriptionFinancials;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HasSubscriptionFinancialsTest extends TestCase
{
    /**
     * @return array<string, array{0: float, 1: string, 2: float, 3: float}>
     */
    public static function billingCycleProvider(): array
    {
        return [
            'weekly' => [12.00, 'weekly', 52.00, 624.00],
            'monthly' => [50.00, 'monthly', 50.00, 600.00],
            'quarterly' => [90.00, 'quarterly', 30.00, 360.00],
            'semi_annual' => [180.00, 'semi_annual', 30.00, 360.00],
            'annual' => [240.00, 'annual', 20.00, 240.00],
        ];
    }

    #[DataProvider('billingCycleProvider')]
    public function test_it_converts_billing_cycles_to_monthly_and_yearly_equivalents(
        float $amount,
        string $cycle,
        float $expectedMonthly,
        float $expectedYearly
    ): void {
        $subject = $this->makeFinancialSubject($amount, 'MYR', $cycle, $amount);

        $this->assertEqualsWithDelta($expectedMonthly, $subject->monthlyAmountIn('MYR'), 0.001);
        $this->assertEqualsWithDelta($expectedYearly, $subject->yearlyAmountIn('MYR'), 0.001);
        $this->assertEqualsWithDelta($expectedMonthly, $subject->monthlyAmount(), 0.001);
        $this->assertEqualsWithDelta($expectedYearly, $subject->yearlyAmount(), 0.001);
    }

    public function test_it_uses_native_amount_without_fx_conversion_when_target_matches_currency(): void
    {
        $subject = $this->makeFinancialSubject(20.00, 'USD', 'monthly', 89.40);

        $this->assertSame(20.00, $subject->effectiveAmountIn('usd'));
        $this->assertSame(20.00, $subject->monthlyAmountIn('USD'));
        $this->assertSame(240.00, $subject->yearlyAmountIn('USD'));
        $this->assertSame(89.40, $subject->effectiveMyr());
        $this->assertSame(89.40, $subject->monthlyAmountMyr());
        $this->assertEqualsWithDelta(1072.80, $subject->yearlyAmountMyr(), 0.001);
    }

    public function test_effective_myr_falls_back_to_amount_when_amount_myr_is_null(): void
    {
        $subject = $this->makeFinancialSubject(45.50, 'MYR', 'monthly', null);

        $this->assertSame(45.50, $subject->effectiveMyr());
    }

    private function makeFinancialSubject(float $amount, string $currency, string $cycle, ?float $amountMyr): object
    {
        return new class($amount, $currency, $cycle, $amountMyr)
        {
            use HasSubscriptionFinancials;

            public function __construct(
                public float $amount,
                public string $currency,
                public string $billing_cycle,
                public ?float $amount_myr,
            ) {}
        };
    }
}
