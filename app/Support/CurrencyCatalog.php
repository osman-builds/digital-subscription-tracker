<?php

declare(strict_types=1);

namespace App\Support;

final class CurrencyCatalog
{
    /**
     * Supported billing currencies for subscription forms and normalization.
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
            'MYR' => 'MYR – Malaysian Ringgit',
            'USD' => 'USD – US Dollar',
            'EUR' => 'EUR – Euro',
            'GBP' => 'GBP – British Pound',
            'SGD' => 'SGD – Singapore Dollar',
            'AUD' => 'AUD – Australian Dollar',
            'JPY' => 'JPY – Japanese Yen',
            'CNY' => 'CNY – Chinese Yuan',
            'HKD' => 'HKD – Hong Kong Dollar',
            'INR' => 'INR – Indian Rupee',
            'CAD' => 'CAD – Canadian Dollar',
            'CHF' => 'CHF – Swiss Franc',
            'KRW' => 'KRW – South Korean Won',
            'TWD' => 'TWD – New Taiwan Dollar',
            'THB' => 'THB – Thai Baht',
            'IDR' => 'IDR – Indonesian Rupiah',
            'PHP' => 'PHP – Philippine Peso',
            'SAR' => 'SAR – Saudi Riyal',
            'AED' => 'AED – UAE Dirham',
            'QAR' => 'QAR – Qatari Riyal',
            'KWD' => 'KWD – Kuwaiti Dinar',
            'EGP' => 'EGP – Egyptian Pound',
            'ZAR' => 'ZAR – South African Rand',
            'NGN' => 'NGN – Nigerian Naira',
            'KES' => 'KES – Kenyan Shilling',
            'SEK' => 'SEK – Swedish Krona',
            'NOK' => 'NOK – Norwegian Krone',
            'DKK' => 'DKK – Danish Krone',
            'NZD' => 'NZD – New Zealand Dollar',
            'TRY' => 'TRY – Turkish Lira',
            'BRL' => 'BRL – Brazilian Real',
            'MXN' => 'MXN – Mexican Peso',
            'PKR' => 'PKR – Pakistani Rupee',
            'BDT' => 'BDT – Bangladeshi Taka',
        ];
    }
}
