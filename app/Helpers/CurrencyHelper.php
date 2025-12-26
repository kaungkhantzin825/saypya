<?php

namespace App\Helpers;

class CurrencyHelper
{
    /**
     * Format price in Myanmar Kyat (MMK)
     */
    public static function formatMMK($amount)
    {
        if ($amount == 0) {
            return 'အခမဲ့';
        }
        
        // Convert USD to MMK (approximate rate: 1 USD = 2100 MMK)
        $mmkAmount = $amount * 2100;
        
        return number_format($mmkAmount) . ' MMK';
    }
    
    /**
     * Format price in Myanmar Kyat with Myanmar numerals
     */
    public static function formatMMKMyanmar($amount)
    {
        if ($amount == 0) {
            return 'အခမဲ့';
        }
        
        $mmkAmount = $amount * 2100;
        $formatted = number_format($mmkAmount);
        
        // Convert to Myanmar numerals
        $myanmarNumerals = [
            '0' => '၀',
            '1' => '၁',
            '2' => '၂',
            '3' => '၃',
            '4' => '၄',
            '5' => '၅',
            '6' => '၆',
            '7' => '၇',
            '8' => '၈',
            '9' => '၉'
        ];
        
        $myanmarFormatted = strtr($formatted, $myanmarNumerals);
        
        return $myanmarFormatted . ' ကျပ်';
    }
}