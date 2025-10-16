<?php declare(strict_types=1);

namespace ddd\pricing;

use ddd\pricing\values\AircraftPricingCalculatorUnit;
use yii\base\Application;
use yii\base\BootstrapInterface;

final class AircraftPricingBootstrap implements BootstrapInterface
{
    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        \Yii::$container->setSingletons([
            'ddd\pricing\services\leg\FlightConditionsChecker' => [
                'class' => 'ddd\pricing\services\leg\FlightConditionsChecker',
                'conditions' => [
                    'inner' => 'ddd\pricing\services\leg\conditions\FlightInnerCondition',
                    'ferry' => 'ddd\pricing\services\leg\conditions\FlightFerryCondition',
                    'holidays' => 'ddd\pricing\services\leg\conditions\FlightHolidaysCondition',
                    'departure_icao' => 'ddd\pricing\services\leg\conditions\FlightDepartureIcaoCondition',
                    'departure_city_id' => 'ddd\pricing\services\leg\conditions\FlightDepartureCityCondition',
                    'departure_country_iso3' => 'ddd\pricing\services\leg\conditions\FlightDepartureCountryCondition',
                    'arrival_icao' => 'ddd\pricing\services\leg\conditions\FlightArrivalIcaoCondition',
                    'arrival_city_id' => 'ddd\pricing\services\leg\conditions\FlightArrivalCityCondition',
                    'arrival_country_iso3' => 'ddd\pricing\services\leg\conditions\FlightArrivalCountryCondition',
                    'cross_country_iso3' => 'ddd\pricing\services\leg\conditions\FlightCrossCountryCondition',
                    'flags' => 'ddd\pricing\services\leg\conditions\FlightFlagsCondition',
                    'aircraft_type_id' => 'ddd\pricing\services\leg\conditions\FlightAircraftTypeCondition',
                    'aircraft_class_id' => 'ddd\pricing\services\leg\conditions\FlightAircraftClassCondition',
                ]
            ],
            'ddd\pricing\services\leg\FlightUnitExtractor' => [
                'class' => 'ddd\pricing\services\leg\FlightUnitExtractor',
                'extractors' => [
                    AircraftPricingCalculatorUnit::AIRWAY_TIME => 'ddd\pricing\services\leg\extractors\FlightAirwayTimeExtractor',
                    AircraftPricingCalculatorUnit::REFUEL_TIME => 'ddd\pricing\services\leg\extractors\FlightRefuelTimeExtractor',
                    AircraftPricingCalculatorUnit::TOTAL_TIME => 'ddd\pricing\services\leg\extractors\FlightTotalTimeExtractor',
                    AircraftPricingCalculatorUnit::LEG => 'ddd\pricing\services\leg\extractors\FlightLegCountExtractor',
                    AircraftPricingCalculatorUnit::PAX => 'ddd\pricing\services\leg\extractors\FlightPaxExtractor',
                    AircraftPricingCalculatorUnit::LUGGAGE => 'ddd\pricing\services\leg\extractors\FlightLuggageExtractor',
                    AircraftPricingCalculatorUnit::TAKEOFF => 'ddd\pricing\services\leg\extractors\FlightTakeoffCountExtractor',
                    AircraftPricingCalculatorUnit::LANDING => 'ddd\pricing\services\leg\extractors\FlightLandingCountExtractor',
                    AircraftPricingCalculatorUnit::NIGHT_STOP => 'ddd\pricing\services\leg\extractors\FlightNightStopsCountExtractor',
                    AircraftPricingCalculatorUnit::PARKING_DAYS => 'ddd\pricing\services\leg\extractors\FlightParkingDaysCountExtractor',
                    AircraftPricingCalculatorUnit::FUEL_STOPS => 'ddd\pricing\services\leg\extractors\FlightFuelStopsCountExtractor',
                    AircraftPricingCalculatorUnit::FLIGHT_TTL => 'ddd\pricing\services\leg\extractors\FlightTtlExtractor',
                    AircraftPricingCalculatorUnit::AIRWAY_DISTANCE_KM => 'ddd\pricing\services\leg\extractors\FlightAirwayDistanceKmExtractor',
                    AircraftPricingCalculatorUnit::AIRWAY_DISTANCE_NM => 'ddd\pricing\services\leg\extractors\FlightAirwayDistanceNmExtractor',
                ]
            ],
            'ddd\pricing\services\trip\TripUnitExtractor' => [
                'class' => 'ddd\pricing\services\trip\TripUnitExtractor',
                'extractors' => [
                    AircraftPricingCalculatorUnit::STARTUP => 'ddd\pricing\services\trip\extractors\TripStartupCountExtractor',
                    AircraftPricingCalculatorUnit::TRIP_DAYS => 'ddd\pricing\services\trip\extractors\TripTripDaysCountExtractor',
                    AircraftPricingCalculatorUnit::FLIGHT_DAYS => 'ddd\pricing\services\trip\extractors\TripFlightDaysCountExtractor',
                    AircraftPricingCalculatorUnit::HOME_DAYS => 'ddd\pricing\services\trip\extractors\TripHomeDaysCountExtractor',
                    AircraftPricingCalculatorUnit::TRIP_PAX => 'ddd\pricing\services\trip\extractors\TripMaxPaxCountExtractor',
                    AircraftPricingCalculatorUnit::CREW_SWAP => 'ddd\pricing\services\trip\extractors\TripCrewSwapCountExtractor',
                    AircraftPricingCalculatorUnit::FLIGHT_TTL => 'ddd\pricing\services\trip\extractors\TripFlightTtlExtractor',
                    AircraftPricingCalculatorUnit::LEGS_COUNT => 'ddd\pricing\services\trip\extractors\TripLegsCountExtractor',
                    AircraftPricingCalculatorUnit::FERRY_LEGS_COUNT => 'ddd\pricing\services\trip\extractors\TripFerryLegsCountExtractor',
                    AircraftPricingCalculatorUnit::TAXI_LEGS_COUNT => 'ddd\pricing\services\trip\extractors\TripTaxiLegsCountExtractor',
                ]
            ],
        ]);
    }
}
