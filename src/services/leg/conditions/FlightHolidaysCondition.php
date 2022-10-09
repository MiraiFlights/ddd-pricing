<?php declare(strict_types=1);

namespace ddd\pricing\services\leg\conditions;

use ddd\adapter\Trip\domain\aggregates\FlightDecomposition;

final class FlightHolidaysCondition implements FlightConditionInterface
{
    public function isSatisfiedBy($value, FlightDecomposition $flight): bool
    {
        $day = $flight->getDepartureDate()->format('D');
        $isHoliday = ($day == 'Fri' || $day == 'Sat' || $day == 'Sun');
        if ($isHoliday && ($value === '0'))
            return false;
        if (!$isHoliday && ($value === '1'))
            return false;

        return true;
    }
}