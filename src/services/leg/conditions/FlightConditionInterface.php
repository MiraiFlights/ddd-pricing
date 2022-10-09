<?php declare(strict_types=1);

namespace ddd\pricing\services\leg\conditions;

use ddd\adapter\Trip\domain\aggregates\FlightDecomposition;

interface FlightConditionInterface
{
    public function isSatisfiedBy($value, FlightDecomposition $flight): bool;
}