<?php declare(strict_types=1);

namespace ddd\pricing\services;

use ddd\adapter\Trip\domain\aggregates\TripDecomposition;
use ddd\pricing\entities\AircraftPricingCalculator;
use ddd\pricing\exceptions\AircraftPricingBadConfigurationException;
use ddd\pricing\interfaces\AircraftPricingHelperInterface;
use ddd\pricing\services\trip\TripUnitExtractor;
use ddd\pricing\values;
use ddd\pricing\values\AircraftPricingCalculatorType;
use unapi\helper\money\Currency;
use unapi\helper\money\MoneyAmount;

final class TripCalculatorService
{
    private FlightCalculatorService $flightCalculatorService;
    private TripUnitExtractor $unitExtractor;
    private FiltersChecker $filtersChecker;
    private AircraftPricingCalculatorRoundService $roundService;
    private AircraftPricingHelperInterface $helper;

    public function __construct(
        FlightCalculatorService               $flightCalculatorService,
        TripUnitExtractor                     $unitExtractor,
        FiltersChecker                        $filtersChecker,
        AircraftPricingCalculatorRoundService $roundService,
        AircraftPricingHelperInterface        $helper
    )
    {
        $this->flightCalculatorService = $flightCalculatorService;
        $this->unitExtractor = $unitExtractor;
        $this->filtersChecker = $filtersChecker;
        $this->roundService = $roundService;
        $this->helper = $helper;
    }

    /**
     * @param TripDecomposition $trip
     * @param MoneyAmount|null $minPrice
     * @return DetailedPrice
     * @throws AircraftPricingBadConfigurationException
     */
    public function getPrice(TripDecomposition $trip, ?MoneyAmount $minPrice = null): DetailedPrice
    {
        $details = ['flights' => [], 'trip' => [], 'tax' => []];
        $legsPrice = 0;
        $taxable = 0;

        $calculators = iterator_to_array($this->helper->getActualCalculators($trip), false);
        $this->guardAirwayTimeCalculatorExists($calculators);

        /** @var AircraftPricingCalculator[] $legCalculators */
        $legCalculators = array_filter($calculators, fn(AircraftPricingCalculator $calculator) => $calculator->getProperties()->getType()->getValue() === AircraftPricingCalculatorType::LEG);
        /** @var AircraftPricingCalculator[] $legMarginCalculators */
        $legMarginCalculators = array_filter($calculators, fn(AircraftPricingCalculator $calculator) => $calculator->getProperties()->getType()->getValue() === AircraftPricingCalculatorType::LEG_MARGIN);
        /** @var AircraftPricingCalculator[] $tripCalculators */
        $tripCalculators = array_filter($calculators, fn(AircraftPricingCalculator $calculator) => $calculator->getProperties()->getType()->getValue() === AircraftPricingCalculatorType::TRIP);
        /** @var AircraftPricingCalculator[] $tripMarginCalculators */
        $tripMarginCalculators = array_filter($calculators, fn(AircraftPricingCalculator $calculator) => $calculator->getProperties()->getType()->getValue() === AircraftPricingCalculatorType::TRIP_MARGIN);

        foreach ($trip->getFlights() as $flight) {
            $flightDetails = ['calculators' => []];
            $legPrice = 0;
            foreach ($legCalculators as $legCalculator) {
                $price = $this->flightCalculatorService->calculatePrice($flight, $legCalculator);
                if (null === $price)
                    continue;

                switch ($legCalculator->getProperties()->getTax()->getValue()) {
                    case values\AircraftPricingCalculatorTax::IS_TAXABLE:
                        $taxable += $price->getAmount();
                        break;
                    case values\AircraftPricingCalculatorTax::IS_TAX:
                        $details['tax'][] = DetailedPrice::fromMoneyAmount($price)->setDetails($legCalculator->jsonSerialize());
                        break;
                }

                $flightDetails['calculators'][] = DetailedPrice::fromMoneyAmount($price)->setDetails($legCalculator->jsonSerialize());
                $legPrice += $price->getAmount();
            }

            $marginPrice = 0;
            foreach ($legMarginCalculators as $legMarginCalculator) {
                $margin = $this->flightCalculatorService->calculateMargin($flight, $legMarginCalculator, new MoneyAmount($legPrice, new Currency(Currency::EUR)));
                if (null === $margin)
                    continue;
                $marginPrice += $margin->getAmount();
                $flightDetails['calculators'][] = DetailedPrice::fromMoneyAmount($margin)->setDetails($legMarginCalculator->jsonSerialize());
            }
            $legPrice += $marginPrice;

            $legsPrice += $legPrice;
            $details['flights'][] = (new DetailedPrice($legPrice, new Currency(Currency::EUR)))->setDetails($flightDetails);
        }

        $tripPrice = array_reduce(
            $tripCalculators,
            function (float $initial, AircraftPricingCalculator $tripCalculator) use ($trip, &$details, &$taxable, &$tax) {
                if (!$this->checkCalculatorFilters($trip, $tripCalculator))
                    return $initial;

                $amount = $this->extractPrice($trip, $tripCalculator) * $this->roundService->applyRoundMethod(
                        $this->unitExtractor->extractUnit($trip, $tripCalculator->getProperties()->getUnit()->getValue()),
                        $tripCalculator->getProperties()->getRound()->getValue()
                    );

                switch ($tripCalculator->getProperties()->getTax()->getValue()) {
                    case values\AircraftPricingCalculatorTax::IS_TAXABLE:
                        $taxable += $amount;
                        break;
                    case values\AircraftPricingCalculatorTax::IS_TAX:
                        $details['tax'][] = (new DetailedPrice($amount, new Currency(Currency::EUR)))->setDetails($tripCalculator->jsonSerialize());
                        break;
                }

                $details['trip'][] = (new DetailedPrice($amount, new Currency(Currency::EUR)))->setDetails($tripCalculator->jsonSerialize());
                return $initial + $amount;
            },
            0.0
        );

        $taxPrice = array_reduce(
            $tripMarginCalculators,
            function (float $initial, AircraftPricingCalculator $taxCalculator) use ($trip, &$details, $taxable) {
                $amount = $this->extractPrice($trip, $taxCalculator) * $taxable / 100;
                $details['tax'][] = (new DetailedPrice($amount, new Currency(Currency::EUR)))->setDetails($taxCalculator->jsonSerialize());
                return $initial + $amount;
            },
            0.0
        );

        $result = floor($legsPrice + $tripPrice + $taxPrice);
        if ($minPrice && ($result < $minPrice->getAmount()))
            $result = $minPrice->getAmount();

        return (new DetailedPrice($result, new Currency(Currency::EUR)))->setDetails($details);
    }

    private function extractPrice(TripDecomposition $trip, AircraftPricingCalculator $calculator): float
    {
        return $calculator->getProperties()->getPrice()->getAmount();
    }

    private function checkCalculatorFilters(TripDecomposition $trip, AircraftPricingCalculator $calculator): bool
    {
        foreach ($calculator->getProperties()->getFilters() as $filter) {
            $value = $this->unitExtractor->extractUnit($trip, $filter['unit']);

            if (!$this->filtersChecker->checkCalculatorFilters($value, $filter))
                return false;
        }
        return true;
    }

    /**
     * Защита от дурака #FT-2536
     * @param AircraftPricingCalculator[] $calculators
     * @return void
     * @throws AircraftPricingBadConfigurationException
     */
    private function guardAirwayTimeCalculatorExists(array $calculators)
    {
        foreach ($calculators as $calculator) {
            if ($calculator->getProperties()->getUnit()->getValue() === values\AircraftPricingCalculatorUnit::AIRWAY_TIME)
                return;
        }

        throw new AircraftPricingBadConfigurationException();
    }
}