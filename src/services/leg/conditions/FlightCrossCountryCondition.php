<?php declare(strict_types=1);

namespace ddd\pricing\services\leg\conditions;

use ddd\adapter\Trip\domain\aggregates\FlightDecomposition;

final class FlightCrossCountryCondition implements FlightConditionInterface
{
    public function isSatisfiedBy($value, FlightDecomposition $flight): bool
    {
        $intersect = array_intersect($flight->getRoute()->getCountriesOnTheWay()->asArray(), (array)$value['array']);
        return (bool)$value['inverse'] xor !empty($intersect);
    }
}