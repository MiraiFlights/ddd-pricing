<?php declare(strict_types=1);

namespace ddd\pricing\services\guards;

use ddd\pricing\entities\AircraftPricingCalculator;
use ddd\pricing\exceptions\AircraftPricingBadConfigurationException;
use ddd\pricing\values\AircraftPricingCalculatorUnit;

final class TripCalculatorGuards
{
    /**
     * Защита от дурака #FT-2536
     * @param AircraftPricingCalculator[] $calculators
     * @return void
     * @throws AircraftPricingBadConfigurationException
     */
    public static function guardAirwayTimeCalculatorExists(array $calculators): void
    {
        foreach ($calculators as $calculator) {
            if ($calculator->getProperties()->getUnit()->getValue() === AircraftPricingCalculatorUnit::AIRWAY_TIME)
                return;
        }

        throw new AircraftPricingBadConfigurationException();
    }
}