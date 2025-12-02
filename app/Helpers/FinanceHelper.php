<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinanceHelper
{
    /**
     * Generate unique account number (16 digits)
     * Format: BankCode (3) + Random (12) + Check Digit (1)
     *
     * @param string|null $bankCode
     * @return string
     */
    public static function generateAccountNumber(?string $bankCode = '001'): string
    {
        do {
            // Generate 12 random digits
            $randomDigits = str_pad(random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);

            // Combine with bank code
            $baseNumber = $bankCode . $randomDigits;

            // Calculate check digit using Luhn algorithm
            $checkDigit = self::calculateLuhnCheckDigit($baseNumber);

            // Final account number
            $accountNumber = $baseNumber . $checkDigit;

            // Check uniqueness in database
            $exists = DB::table('finances')
                ->where('finance_number', $accountNumber)
                ->exists();

        } while ($exists);

        return $accountNumber;
    }

    /**
     * Calculate Luhn check digit
     *
     * @param string $number
     * @return int
     */
    private static function calculateLuhnCheckDigit(string $number): int
    {
        $sum = 0;
        $length = strlen($number);

        for ($i = 0; $i < $length; $i++) {
            $digit = (int)$number[$length - 1 - $i];

            if ($i % 2 == 0) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return (10 - ($sum % 10)) % 10;
    }

    /**
     * Generate unique finance identifier (max 150 chars)
     * Format: FIN-[timestamp]-[random]-[hash]
     *
     * @param int|null $userId
     * @return string
     */
    public static function generateFinanceUnique(?int $userId = null): string
    {
        do {
            $timestamp = now()->format('YmdHis');
            $random = Str::random(8);
            $hash = substr(hash('sha256', microtime() . $userId . Str::random(16)), 0, 12);

            // Build the unique string
            $uniqueString = "FIN-{$timestamp}-{$random}-{$hash}";

            // Ensure length is max 150 chars
            if (strlen($uniqueString) > 150) {
                $uniqueString = substr($uniqueString, 0, 150);
            }

            // Check uniqueness
            $exists = DB::table('finances')
                ->where('finance_unique', $uniqueString)
                ->exists();

        } while ($exists);

        return $uniqueString;
    }

    /**
     * Generate both account number and unique string
     *
     * @param string|null $bankCode
     * @param int|null $userId
     * @return array
     */
    public static function generateFinanceIdentifiers(?string $bankCode = '001', ?int $userId = null): array
    {
        return [
            'account_number' => self::generateAccountNumber($bankCode),
            'finance_unique' => self::generateFinanceUnique($userId)
        ];
    }

    /**
     * Format account number for display
     * Format: XXXX-XXXX-XXXX-XXXX
     *
     * @param string $accountNumber
     * @return string
     */
    public static function formatAccountNumber(string $accountNumber): string
    {
        return implode('-', str_split($accountNumber, 4));
    }

    /**
     * Validate account number using Luhn algorithm
     *
     * @param string $accountNumber
     * @return bool
     */
    public static function validateAccountNumber(string $accountNumber): bool
    {
        $number = preg_replace('/[^0-9]/', '', $accountNumber);

        if (strlen($number) !== 16) {
            return false;
        }

        $baseNumber = substr($number, 0, 15);
        $checkDigit = substr($number, 15, 1);

        $calculatedCheckDigit = self::calculateLuhnCheckDigit($baseNumber);

        return $calculatedCheckDigit == $checkDigit;
    }

    /**
     * Generate batch of unique account numbers
     *
     * @param int $count
     * @param string|null $bankCode
     * @return array
     */
    public static function generateBatchAccountNumbers(int $count = 10, ?string $bankCode = '001'): array
    {
        $numbers = [];

        for ($i = 0; $i < $count; $i++) {
            $numbers[] = self::generateAccountNumber($bankCode);
        }

        return $numbers;
    }

    /**
     * Extract date from finance unique string
     *
     * @param string $financeUnique
     * @return \Carbon\Carbon|null
     */
    public static function extractDateFromUnique(string $financeUnique): ?\Carbon\Carbon
    {
        $parts = explode('-', $financeUnique);

        if (count($parts) >= 2 && isset($parts[1])) {
            try {
                return \Carbon\Carbon::createFromFormat('YmdHis', $parts[1]);
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }
}
