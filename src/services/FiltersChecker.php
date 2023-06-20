<?php declare(strict_types=1);

namespace ddd\pricing\services;

use ddd\pricing\exceptions\AircraftPricingCalculationException;

final class FiltersChecker
{
    public function checkCalculatorFilters($value, array $filter): bool
    {
            switch ($filter['comparison']) {
                case '=':
                    return $value == $filter['value'];
                case '>':
                    return $value > $filter['value'];
                case '<':
                    return $value < $filter['value'];
                case '>=':
                    return $value >= $filter['value'];
                case '<=':
                    return $value <= $filter['value'];
                case '!=':
                    return $value != $filter['value'];
                default:
                    throw new AircraftPricingCalculationException('Unknown comparison: ' . $filter['comparison']);
            }
    }
}