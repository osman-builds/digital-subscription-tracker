<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\FxRateService;
use Illuminate\Console\Command;

class RefreshFxRates extends Command
{
    protected $signature = 'fx:refresh {--force : Bypass cache and force API call}';

    protected $description = 'Refresh FX rates from ExchangeRate-API and update amount_myr on all subscriptions';

    public function handle(FxRateService $fx): int
    {
        $this->info('Refreshing FX rates...');

        $rates = $this->option('force') ? $fx->refresh() : $fx->getRates();

        $this->line('Rates loaded (MYR per 1 unit):');
        foreach (['USD', 'EUR', 'GBP', 'SGD', 'AUD', 'JPY', 'CNY', 'MYR'] as $code) {
            if (isset($rates[$code])) {
                $this->line("  {$code} → RM ".number_format($rates[$code], 4));
            }
        }

        $this->info('Updating subscription MYR amounts...');

        $subscriptions = Subscription::whereNotNull('currency')->get();
        $updated = 0;

        foreach ($subscriptions as $sub) {
            $amountMyr = $fx->toMyr((float) $sub->amount, $sub->currency);
            $sub->updateQuietly(['amount_myr' => $amountMyr]);
            $updated++;
        }

        $this->info("Updated {$updated} subscription(s).");

        return self::SUCCESS;
    }
}
