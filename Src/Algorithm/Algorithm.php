<?php

namespace Extra\Src\Algorithm;


/**
 *  Warframe collection
 *
 *  Algorithm
 *
 *  @package Extra\Src
 *  @version 1.5
 *  @author itachi
 */
class Algorithm
{
    /** @var string */
    private static string $alphabet;
    /** @var int */
    private static int $alphabetLength;
    /** @var array|null */
    private static ?array $lookup = null;
    /** @var int|null */
    private static ?int $totalWeight = null;

    /**
     * @param int $length
     * @param string|null $alphabet
     * @return string
     */
    public static function random(int $length, ?string $alphabet = null): string
    {
        if ($alphabet) self::setAlphabet($alphabet);
        else self::setAlphabet((
            implode(range('a', 'z'))
            . implode(range('A', 'Z'))
            . implode(range(0, 9))
        ));

        $token = '';

        for ($i = 0; $i < $length; $i++) {
            $randomKey = self::getRandomInteger(0, self::$alphabetLength);
            $token .= self::$alphabet[$randomKey];
        }

        return $token;
    }

    /**
     * Randomly selects one of the elements based on their weight.
     * Optimized for a large number of elements.
     *
     * @param array $values index array of elements
     * @param array<int|float> $weights index array of corresponding weights
     * @return mixed selected item
     */
    public static function weightedRandom(array $values, array $weights): mixed
    {
        $totalWeight = array_sum($weights);
        $randomValue = mt_rand() / mt_getrandmax() * $totalWeight;
        foreach ($weights as $key => $weight) {
            $randomValue -= $weight;
            if ($randomValue <= 0) return $values[$key];
        }
        return null;
    }

    private static function setAlphabet(string $alphabet): void
    {
        self::$alphabet = $alphabet;
        self::$alphabetLength = strlen($alphabet);
    }

    private static function getRandomInteger(int $min, int $max): int
    {
        $range = ($max - $min);

        if ($range < 0) return $min;
        $log = log($range, 2);

        // Length in bytes.
        $bytes = (int) ($log / 8) + 1;
        // Length in bits.
        $bits = (int) $log + 1;
        // Set all lower bits to 1.
        $filter = (int) (1 << $bits) - 1;

        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            // Discard irrelevant bits.
            $rnd = $rnd & $filter;
        } while ($rnd >= $range);

        return ($min + $rnd);
    }
}