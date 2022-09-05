<?php declare(strict_types=1);

namespace ddd\pricing\services\leg\conditions;

use ddd\aviation\aggregates\FlightDecomposition;

final class FlightCrossCountryCondition implements FlightConditionInterface
{
    public function isSatisfiedBy($value, FlightDecomposition $flight): bool
    {
        $intersect = array_intersect(array_map(fn($country): string => $country->getValue(), $flight->getRoute()->getCountriesOnTheWay()), (array)$value['array']);
        return (bool)$value['inverse'] xor !empty($intersect);
    }
}