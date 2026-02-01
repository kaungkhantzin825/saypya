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
        
        // Display the amount as-is (already in MMK)
        return number_format($amount) . ' Ks';
    }
    
    /**
     * Format price in Myanmar Kyat with Myanmar numerals
     */
    public static function formatMMKMyanmar($amount)
    {
        if ($amount == 0) {
            return 'အခမဲ့';
        }
        
        $formatted = number_format($amount);
        
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