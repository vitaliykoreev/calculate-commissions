<?php

const BIN_PROVIDER_API_URL = 'https://lookup.binlist.net/';
const EXCHANGE_RATE_API_KEY = '7d3b20f4456375473fcfca1ad4d8e68d';
const EXCHANGE_RATE_API_URL = 'http://api.exchangeratesapi.io/v1/latest';

function getCountryCodeByBin(string $bin): ?string
{
    $response = file_get_contents(BIN_PROVIDER_API_URL . $bin);
    try {
        $parsedData = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        print_r('Wrong JSON format' . PHP_EOL);

        return null;
    }

    return $parsedData['country']['alpha2'] ?? null;
}

function getExchangeRateByCurrency(string $currency): ?float
{
    $params = ['base' => 'EUR', 'symbols' => $currency, 'access_key' => EXCHANGE_RATE_API_KEY];
    $response = file_get_contents(EXCHANGE_RATE_API_URL . '?' . http_build_query($params));
    try {
        $parsedData = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        print_r('Wrong JSON format' . PHP_EOL);

        return null;
    }

    return $parsedData['rates'][strtoupper($currency)] ?? null;
}

function calculateCommissionByCountry(float $amount, string $currency, string $countryCode): ?float
{
    $isEu = isEu($countryCode);
    $rate = getExchangeRateByCurrency($currency);
    if ($rate === null) {
        print_r('Can`t receive exchange rate for ' . $currency . PHP_EOL);

        return null;
    }
    $amount = $rate > 0 ? ($amount / $rate) : $amount;
    $commission = $isEu ? .01 : .02;

    return round($amount * $commission, 2);
}

function isEu(?string $countryCode): bool
{
    $euCountries = ['AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PO', 'PT', 'RO', 'SE', 'SI', 'SK'];

    return in_array($countryCode, $euCountries, true);
}

function calculateCommissions(string $filepath): void
{
    $file = fopen($filepath, 'rb');
    if (!$file) {
        printf('File "%s" not found' . PHP_EOL, $filepath);

        return;
    }

    // Readying input file by row
    while (!feof($file))
    {
        $row = fgets($file);
        if (empty(trim($row))) {
            continue;
        }
        // Trying to decode row
        try {
            $rowData = json_decode($row, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            print_r('Wrong JSON format' . PHP_EOL);
            continue;
        }
        $countryCode = getCountryCodeByBin($rowData['bin']);
        // Output calculated commission
        print_r(calculateCommissionByCountry((float) $rowData['amount'], $rowData['currency'], $countryCode) . PHP_EOL);
    }
}

$filepath = $argv[1] ?? null;
if ($filepath) {
    calculateCommissions($filepath);
}
