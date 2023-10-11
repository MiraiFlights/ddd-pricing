<?php declare(strict_types=1);

namespace ddd\pricing\entities;

use ddd\pricing\values;

final class AircraftPricingCalculator implements \JsonSerializable
{
    private values\AircraftPricingCalculatorID $id;
    private values\AircraftPricingCalculatorProperties $properties;

    /**
     * AircraftPricingCalculator constructor.
     * @param values\AircraftPricingCalculatorID $id
     * @param values\AircraftPricingCalculatorProperties $properties
     */
    private function __construct(values\AircraftPricingCalculatorID $id, values\AircraftPricingCalculatorProperties $properties)
    {
        $this->id = $id;
        $this->properties = $properties;
    }

    /**
     * @param values\AircraftPricingCalculatorID $id
     * @param values\AircraftPricingCalculatorProperties $properties
     * @return AircraftPricingCalculator
     */
    public static function load(values\AircraftPricingCalculatorID $id, values\AircraftPricingCalculatorProperties $properties)
    {
        return new self($id, $properties);
    }

    /**
     * @return values\AircraftPricingCalculatorID
     */
    public function getId(): values\AircraftPricingCalculatorID
    {
        return $this->id;
    }

    /**
     * @return values\AircraftPricingCalculatorProperties
     */
    public function getProperties(): values\AircraftPricingCalculatorProperties
    {
        return $this->properties;
    }

   #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return array_merge(
            [
                'id' => $this->getId()->getValue(),
                'type_id' => $this->getProperties()->getType()->getValue(),
                'name' => $this->getProperties()->getName()->getValue(),
                'price' => $this->getProperties()->getPrice()->getAmount(),
                'pricingProfileId' => $this->getProperties()->getAircraftPricingProfileID() ? $this->getProperties()->getAircraftPricingProfileID()->getValue() : null,
            ],
            array_filter([
                'conditions' => $this->getProperties()->getConditions(),
                'filters' => $this->getProperties()->getFilters(),
                'unit' => $this->getProperties()->getUnit()->getValue(),
                'tax' => $this->getProperties()->getTax()->getValue(),
                'round' => $this->getProperties()->getRound()->getValue(),
            ])
        );
    }
}