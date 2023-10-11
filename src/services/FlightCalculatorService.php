<?php declare(strict_types=1);

namespace ddd\pricing\services;

use ddd\adapter\Trip\domain\aggregates\FlightDecomposition;
use ddd\pricing\entities\AircraftPricingCalculator;
use unapi\helper\money\Currency;
use unapi\helper\money\MoneyAmount;

final class FlightCalculatorService
{
    private leg\FlightConditionsChecker $conditionsChecker;
    private leg\FlightUnitExtractor $unitExtractor;
    private FiltersChecker $filtersChecker;
    private AircraftPricingCalculatorRoundService $roundService;

    public function __construct(
        leg\FlightConditionsChecker           $flightConditionsChecker,
        leg\FlightUnitExtractor               $flightUnitExtractor,
        FiltersChecker                        $flightFiltersChecker,
        AircraftPricingCalculatorRoundService $roundService
    )
    {
        $this->conditionsChecker = $flightConditionsChecker;
        $this->unitExtractor = $flightUnitExtractor;
        $this->filtersChecker = $flightFiltersChecker;
        $this->roundService = $roundService;
    }

    public function calculatePrice(FlightDecomposition $flight, AircraftPricingCalculator $calculator): ?MoneyAmount
    {
        if (!$this->conditionsChecker->checkCalculatorConditions($flight, $calculator))
            return null;
        if (!$this->checkCalculatorFilters($flight, $calculator))
            return null;

        $price = $this->extractPrice($flight, $calculator) * $this->roundService->applyRoundMethod(
                $this->unitExtractor->extractUnit($flight, $calculator->getProperties()->getUnit()->getValue()),
                $calculator->getProperties()->getRound()->getValue()
            );

        return new MoneyAmount($price, new Currency(Currency::EUR));
    }

    private function checkCalculatorFilters(FlightDecomposition $flight, AircraftPricingCalculator $calculator): bool
    {
        foreach ($calculator->getProperties()->getFilters() as $filter) {
            $value = $this->unitExtractor->extractUnit($flight, $filter['unit']);

            if (!$this->filtersChecker->checkCalculatorFilters($value, $filter))
                return false;
        }
        return true;
    }

    private function extractPrice(FlightDecomposition $flight, AircraftPricingCalculator $calculator): float
    {
        return $calculator->getProperties()->getPrice()->getAmount();
    }
}