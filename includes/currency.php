<?php
// Exchange rates relative to BDT
define('CURRENCY_RATES', [
    'BDT' => 1,
    'USD' => 0.0091,
    'EUR' => 0.0084,
    'GBP' => 0.0072,
]);
define('CURRENCY_SYMBOLS', [
    'BDT' => '৳',
    'USD' => '$',
    'EUR' => '€',
    'GBP' => '£',
]);

function getActiveCurrency(): string {
    global $pdo;
    static $currency = null;
    if ($currency === null) {
        try {
            $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'active_currency'");
            $stmt->execute();
            $row = $stmt->fetch();
            $currency = $row ? $row['setting_value'] : 'BDT';
        } catch (Exception $e) {
            $currency = 'BDT';
        }
    }
    return $currency;
}

function convertAmount(float $amountBDT, string $currency = null): float {
    if ($currency === null) $currency = getActiveCurrency();
    $rates = CURRENCY_RATES;
    return $amountBDT * ($rates[$currency] ?? 1);
}

function formatPrice(float $amountBDT, string $currency = null): string {
    if ($currency === null) $currency = getActiveCurrency();
    $converted = convertAmount($amountBDT, $currency);
    $symbol = CURRENCY_SYMBOLS[$currency] ?? $currency . ' ';
    if ($currency === 'BDT') {
        return $symbol . number_format($converted, 0);
    }
    return $symbol . number_format($converted, 2);
}

function getStripeAmount(float $amountBDT): int {
    $usd = convertAmount($amountBDT, 'USD');
    return (int) round($usd * 100);
}

function getStripeCurrencyCode(): string {
    return 'usd';
}
