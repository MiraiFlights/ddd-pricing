<?php declare(strict_types=1);

namespace ddd\pricing\mappers;

use ddd\pricing\exceptions\AircraftPricingValidationException;
use ddd\pricing\values\AircraftPricingCalculatorType;

final class AircraftPricingProfileCalculatorTypeMapper
{
    public static function getDefinitions(): array
    {
        return [
            AircraftPricingCalculatorType::LEG => 'Leg price',
            AircraftPricingCalculatorType::LEG_MARGIN => 'Leg margin',
            AircraftPricingCalculatorType::TRIP => 'Trip price',
            AircraftPricingCalculatorType::TRIP_MARGIN => 'Trip margin',
        ];
    }

    /**
     * @param $value
     * @return string
     */
    public static function coreToWeb($value): string
    {
        if (array_key_exists($value, static::getDefinitions()))
            return static::getDefinitions()[$value];

        return 'Unknown';
    }

    /**
     * @param $value
     * @return mixed
     * @throws AircraftPricingValidationException
     */
    public static function webToCore($value)
    {
        $result = array_search($value, static::getDefinitions(), true);
        if (false === $result)
            throw new AircraftPricingValidationException('INVALID_CALCULATOR_TYPE');

        return $result;
    }
}