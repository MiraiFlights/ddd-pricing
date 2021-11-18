<?php declare(strict_types=1);

namespace ddd\pricing\values;

use rubin\structures\enum\Enum;

final class AircraftPricingCalculatorUnit extends Enum
{
    //  for leg
    const AIRWAY_TIME = 'airway_time';
    const REFUEL_TIME = 'refuel_time';
    const TOTAL_TIME = 'total_time';
    const LEG = 'leg';
    const PAX = 'pax';
    const LUGGAGE = 'luggage';
    const TAKEOFF = 'takeoff';
    const LANDING = 'landing';
    const NIGHT_STOP = 'night_stop';
    const PARKING_DAYS = 'parking_days';
    const FUEL_STOPS = 'fuel_stops';

    // for trip
    const STARTUP = 'startup';
    const TRIP_DAYS = 'trip_days';
    const FLIGHT_DAYS = 'flight_days';
    const HOME_DAYS = 'home_days';
    const TRIP_PAX = 'trip_pax';
    const CREW_SWAP = 'crew_swap';

    // for tax
    const PERCENT = 'percent';
}