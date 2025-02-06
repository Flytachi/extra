<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Unit;

/**
 * Class Algorithm
 *
 * The `Algorithm` class provides a set of utility functions associated with random data generation and manipulation.
 * It includes mechanisms to generate random strings,
 * performing weighted selection from an array, and implementing a binary search.
 *
 * The methods provided by `Algorithm` include:
 *
 * - `random(int $length, ?string $alphabet = null): string`: Generates a random string of the specified length
 * using an optional alphabet.
 * - `weightedRandomLite(array $values, array $weights): mixed`: Randomly selects one element from a given array
 * based on their weights in a lightweight manner. This function is optimized for a large number of elements.
 * - `weightedRandom(array $values, array $weights): mixed`: Randomly selects one element from a given array based
 * on their weights. This function is optimized for a large number of elements.
 * - `weightedCalculateProbabilities(array $values, array $weights, bool $isCombine = false): array`: Calculates the
 * probabilities of elements based on their weights.
 * - `binarySearch(array $arr, int|float $value): int`: Performs a binary search on the array for a given
 * value and returns the corresponding index.
 *
 * @version 2.2
 * @author Flytachi
 */
abstract class Algorithm
{
    /** @var string */
    private static string $alphabet;
    /** @var int */
    private static int $alphabetLength;

    /**
     * @param int $length
     * @param string|null $alphabet
     * @return string
     */
    public static function random(int $length, ?string $alphabet = null): string
    {
        if ($alphabet) {
            self::setAlphabet($alphabet);
        } else {
            self::setAlphabet((
            implode(range('a', 'z'))
            . implode(range('A', 'Z'))
            . implode(range(0, 9))
            ));
        }

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
     * @template Item
     * @param array<Item> $values index array of elements
     * @param array<int|float> $weights index array of corresponding weights
     * @return Item selected item
     */
    public static function weightedRandomLite(array $values, array $weights)
    {
        $totalWeight = array_sum($weights);
        $randomValue = mt_rand() / mt_getrandmax() * $totalWeight;
        foreach ($weights as $key => $weight) {
            $randomValue -= $weight;
            if ($randomValue <= 0) {
                return $values[$key];
            }
        }
        return null;
    }

    /**
     * Randomly selects one of the elements based on their weight.
     * Optimized for a large number of elements.
     *
     * ! WARNING !:thousandths of a decimal point (0.001)
     *
     * @template Item
     * @param array<Item> $values index array of elements
     * @param array<int|float> $weights index array of corresponding weights (range )
     * @return Item selected item
     */
    public static function weightedRandom(array $values, array $weights)
    {
        $cum_weights = array();
        $total = 0;
        foreach ($weights as $weight) {
            $total += $weight;
            $cum_weights[] = $total;
        }

        $rand = mt_rand(0, $total * 1000 - 1) / 1000.0;
        $index = self::binarySearch($cum_weights, $rand);
        return $values[$index];
    }

    public static function weightedCalculateProbabilities(array $values, array $weights, bool $isCombine = false): array
    {
        $totalWeight = array_sum($weights);
        $probabilities = [];
        foreach ($weights as $key => $weight) {
            if ($isCombine) {
                $probabilities[$key] = [
                    'value' => $values[$key],
                    'calculate' => ($weight / $totalWeight) * 100,
                ];
            } else {
                $probabilities[$key] = ($weight / $totalWeight) * 100;
            }
        }
        return $probabilities;
    }

    public static function binarySearch(array $arr, int|float $value): int
    {
        $low = 0;
        $high = count($arr) - 1;

        while ($low <= $high) {
            $mid = intval(($low + $high) / 2);
            if ($arr[$mid] < $value) {
                $low = $mid + 1;
            } else {
                $high = $mid - 1;
            }
        }

        return ($arr[$low] >= $value) ? $low : -1;
    }

    private static function setAlphabet(string $alphabet): void
    {
        self::$alphabet = $alphabet;
        self::$alphabetLength = strlen($alphabet);
    }

    private static function getRandomInteger(int $min, int $max): int
    {
        $range = ($max - $min);

        if ($range < 0) {
            return $min;
        }
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
