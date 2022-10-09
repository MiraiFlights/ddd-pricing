<?php declare(strict_types=1);

namespace ddd\pricing\services\leg\conditions;

use ddd\adapter\Trip\domain\aggregates\FlightDecomposition;
use ddd\pricing\interfaces\AircraftPricingHelperInterface;

final class FlightInnerCondition implements FlightConditionInterface
{
    private AircraftPricingHelperInterface $helper;

    public function __construct(AircraftPricingHelperInterface $helper)
    {
        $this->helper = $helper;
    }

    public function isSatisfiedBy($value, FlightDecomposition $flight): bool
    {
        $isInner = $this->helper->isInnerRoute($flight);
        if ($isInner && !(int)$value)
            return false;
        if (!$isInner && (int)$value)
            return false;

        return true;
    }
}