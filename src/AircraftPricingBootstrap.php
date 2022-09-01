<?php declare(strict_types=1);

namespace ddd\pricing;

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
                ]
            ],
            'ddd\pricing\services\leg\FlightUnitExtractor' => [
                'class' => 'ddd\pricing\services\leg\FlightUnitExtractor',
                'extractors' => [
                    'airway_time' => 'ddd\pricing\services\leg\extractors\FlightAirwayTimeExtractor',
                    'refuel_time' => 'ddd\pricing\services\leg\extractors\FlightRefuelTimeExtractor',
                    'total_time' => 'ddd\pricing\services\leg\extractors\FlightTotalTimeExtractor',
                    'leg' => 'ddd\pricing\services\leg\extractors\FlightLegCountExtractor',
                    'pax' => 'ddd\pricing\services\leg\extractors\FlightPaxExtractor',
                    'luggage' => 'ddd\pricing\services\leg\extractors\FlightLuggageExtractor',
                    'takeoff' => 'ddd\pricing\services\leg\extractors\FlightTakeoffCountExtractor',
                    'landing' => 'ddd\pricing\services\leg\extractors\FlightLandingCountExtractor',
                    'night_stop' => 'ddd\pricing\services\leg\extractors\FlightNightStopsCountExtractor',
                    'parking_days' => 'ddd\pricing\services\leg\extractors\FlightParkingDaysCountExtractor',
                    'fuel_stops' => 'ddd\pricing\services\leg\extractors\FlightFuelStopsCountExtractor',
                    'flight_ttl' => 'ddd\pricing\services\leg\extractors\FlightTtlExtractor',
                ]
            ],
            'ddd\pricing\services\trip\TripUnitExtractor' => [
                'class' => 'ddd\pricing\services\trip\TripUnitExtractor',
                'extractors' => [
                    'startup' => 'ddd\pricing\services\trip\extractors\TripStartupCountExtractor',
                    'trip_days' => 'ddd\pricing\services\trip\extractors\TripTripDaysCountExtractor',
                    'flight_days' => 'ddd\pricing\services\trip\extractors\TripFlightDaysCountExtractor',
                    'home_days' => 'ddd\pricing\services\trip\extractors\TripHomeDaysCountExtractor',
                    'trip_pax' => 'ddd\pricing\services\trip\extractors\TripMaxPaxCountExtractor',
                    'crew_swap' => 'ddd\pricing\services\trip\extractors\TripCrewSwapCountExtractor',
                ]
            ],
        ]);
    }
}
