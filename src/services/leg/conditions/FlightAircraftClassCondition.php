<?php declare(strict_types=1);

namespace ddd\pricing\services\leg\conditions;

use ddd\adapter\Trip\domain\aggregates\FlightDecomposition;
use ddd\common\AircraftType\domain\interfaces\AircraftTypeRepositoryInterface;

final class FlightAircraftClassCondition implements FlightConditionInterface
{
    public function __construct(
        private readonly AircraftTypeRepositoryInterface $aircraftTypeRepository,
    )
    {
    }

    public function isSatisfiedBy($value, FlightDecomposition $flight): bool
    {
        $result = in_array(
            $this->aircraftTypeRepository->oneByAircraftTypeId(
                $flight->getAircraft()->getProperties()->getAircraftTypeId()
            )->getId()->getValue(),
            (array)$value['array']
        );
        return (bool)$value['inverse'] ? !$result : $result;
    }
}