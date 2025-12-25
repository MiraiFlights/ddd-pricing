<?php declare(strict_types=1);

namespace ddd\pricing\values;

use unapi\helper\money\MoneyAmount;

final class AircraftPricingCalculatorProperties
{
    private AircraftPricingCalculatorType $type;
    private AircraftPricingCalculatorName $name;
    private array $conditions;
    private array $filters;
    private AircraftPricingCalculatorUnit $unit;
    private AircraftPricingCalculatorTax $tax;
    private AircraftPricingCalculatorRound $round;
    private ?MoneyAmount $price = null;
    private ?int $percent = null;
    private ?AircraftPricingProfileID $aircraftPricingProfileID = null;

    public function __construct(AircraftPricingCalculatorType $type, AircraftPricingCalculatorName $name, array $conditions, array $filters, AircraftPricingCalculatorUnit $unit, AircraftPricingCalculatorTax $tax, AircraftPricingCalculatorRound $round)
    {
        $this->type = $type;
        $this->name = $name;
        $this->conditions = $conditions;
        $this->filters = $filters;
        $this->unit = $unit;
        $this->tax = $tax;
        $this->round = $round;
    }

    /**
     * @return AircraftPricingCalculatorType
     */
    public function getType(): AircraftPricingCalculatorType
    {
        return $this->type;
    }

    /**
     * @return AircraftPricingCalculatorName
     */
    public function getName(): AircraftPricingCalculatorName
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @return AircraftPricingCalculatorUnit
     */
    public function getUnit(): AircraftPricingCalculatorUnit
    {
        return $this->unit;
    }

    /**
     * @return AircraftPricingCalculatorTax
     */
    public function getTax(): AircraftPricingCalculatorTax
    {
        return $this->tax;
    }

    /**
     * @return AircraftPricingCalculatorRound
     */
    public function getRound(): AircraftPricingCalculatorRound
    {
        return $this->round;
    }

    /**
     * @return MoneyAmount|null
     */
    public function getPrice(): ?MoneyAmount
    {
        return $this->price;
    }

    /**
     * @param MoneyAmount|null $price
     * @return AircraftPricingCalculatorProperties
     */
    public function setPrice(?MoneyAmount $price): AircraftPricingCalculatorProperties
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPercent(): ?int
    {
        return $this->percent;
    }

    /**
     * @param int|null $percent
     * @return AircraftPricingCalculatorProperties
     */
    public function setPercent(?int $percent): AircraftPricingCalculatorProperties
    {
        $this->percent = $percent;
        return $this;
    }

    /**
     * @return AircraftPricingProfileID|null
     */
    public function getAircraftPricingProfileID(): ?AircraftPricingProfileID
    {
        return $this->aircraftPricingProfileID;
    }

    /**
     * @param AircraftPricingProfileID|null $aircraftPricingProfileID
     * @return AircraftPricingCalculatorProperties
     */
    public function setAircraftPricingProfileID(?AircraftPricingProfileID $aircraftPricingProfileID): AircraftPricingCalculatorProperties
    {
        $this->aircraftPricingProfileID = $aircraftPricingProfileID;
        return $this;
    }
}