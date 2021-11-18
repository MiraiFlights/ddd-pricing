<?php declare(strict_types=1);

namespace ddd\pricing\services;

use ddd\pricing\entities\AircraftPricingCalculator;
use ddd\pricing\interfaces\AircraftPricingHelperInterface;
use ddd\pricing\services\trip\TripUnitExtractor;
use ddd\pricing\values;
use ddd\aviation\aggregates\TripDecomposition;
use unapi\helper\money\Currency;

final class TripCalculatorService
{
    private FlightCalculatorService $flightCalculatorService;
    private TripUnitExtractor $unitExtractor;
    private AircraftPricingCalculatorRoundService $roundService;
    private AircraftPricingHelperInterface $helper;

    public function __construct(
        FlightCalculatorService $flightCalculatorService,
        TripUnitExtractor $unitExtractor,
        AircraftPricingCalculatorRoundService $roundService,
        AircraftPricingHelperInterface $helper
    )
    {
        $this->flightCalculatorService = $flightCalculatorService;
        $this->unitExtractor = $unitExtractor;
        $this->roundService = $roundService;
        $this->helper = $helper;
    }

    public function getPrice(TripDecomposition $trip): DetailedPrice
    {
        $details = ['flights' => [], 'trip' => [], 'tax' => []];
        $legsPrice = 0;
        $taxable = 0;
        $tax = 0;

        $calculators = iterator_to_array($this->helper->getActualCalculators($trip));
        /** @var AircraftPricingCalculator[] $tripCalculators */
        $tripCalculators = array_filter($calculators, fn(AircraftPricingCalculator $calculator) => $calculator->getProperties()->getType()->getValue() === AircraftPricingCalculatorType::TRIP);
        /** @var AircraftPricingCalculator[] $legCalculators */
        $legCalculators = array_filter($calculators, fn(AircraftPricingCalculator $calculator) => $calculator->getProperties()->getType()->getValue() === AircraftPricingCalculatorType::LEG);
        /** @var AircraftPricingCalculator[] $taxCalculators */
        $taxCalculators = array_filter($calculators, fn(AircraftPricingCalculator $calculator) => $calculator->getProperties()->getType()->getValue() === AircraftPricingCalculatorType::TAX);

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
                        $tax += $price->getAmount();
                        $details['tax'][] = DetailedPrice::fromMoneyAmount($price)->setDetails(['calculator' => $legCalculator]);
                        break;
                }

                $flightDetails['calculators'][] = [
                    'calculator' => $legCalculator,
                    'price' => $price->getAmount(),
                ];
                $legPrice += $price->getAmount();
            }
            $legsPrice += $legPrice;
            $details['flights'][] = (new DetailedPrice($legPrice, new Currency(Currency::EUR)))->setDetails($flightDetails);
        }

        $tripPrice = array_reduce(
            $tripCalculators,
            function (float $initial, AircraftPricingCalculator $tripCalculator) use ($trip, &$details, &$taxable, &$tax) {
                $amount = $this->extractPrice($trip, $tripCalculator) * $this->roundService->applyRoundMethod(
                        $this->unitExtractor->extractUnit($trip, $tripCalculator->getProperties()->getUnit()->getValue()),
                        $tripCalculator->getProperties()->getRound()->getValue()
                    );

                switch ($tripCalculator->getProperties()->getTax()->getValue()) {
                    case values\AircraftPricingCalculatorTax::IS_TAXABLE:
                        $taxable += $amount;
                        break;
                    case values\AircraftPricingCalculatorTax::IS_TAX:
                        $tax += $amount;
                        $details['tax'][] = (new DetailedPrice($amount, new Currency(Currency::EUR)))->setDetails(['calculator' => $tripCalculator]);
                        break;
                }

                $details['trip'][] = [
                    'calculator' => $tripCalculator,
                    'price' => $amount,
                ];

                return $initial + $amount;
            },
            0.0
        );

        $taxPrice = array_reduce(
            $taxCalculators,
            function (float $initial, AircraftPricingCalculator $taxCalculator) use ($trip, &$details, &$taxable, &$tax) {
                $amount = $this->extractPrice($trip, $taxCalculator) * $taxable / 100;
                $details['tax'][] = [
                    'calculator' => $taxCalculator,
                    'price' => $amount,
                ];
                return $initial + $amount;
            },
            0.0
        );

        return (new DetailedPrice(floor($legsPrice + $tripPrice + $taxPrice), new Currency(Currency::EUR)))
            ->setDetails($details);
    }
/*
    private function getActualCalculators(TripDecomposition $trip): \Generator
    {
        $flights = $trip->getFlights();
        $firstFlight = reset($flights);
        $lastFlight = end($flights);
        $tripConditions = new values\AircraftPricingTripConditions($trip->isOneway());

        foreach ($this->aircraftPricingRepository->allByAircraftId($trip->getAircraft()->getId()) as $pricing) {
            if ($pricing->getProperties()->getValidFrom() && $pricing->getProperties()->getValidTo())
                if ($pricing->getProperties()->getValidFrom() < $lastFlight->getArrivalDate() && $pricing->getProperties()->getValidTo() > $firstFlight->getDepartureDate())
                    continue;

            if (!$tripConditions->satisfiedBy($pricing->getProperties()->getConditions()))
                continue;

            foreach ($this->aircraftPricingCalculatorRepository->allByAircraftPricingProfileId($pricing->getProperties()->getPricingProfileId()) as $calculator)
                yield $calculator;
        }
    }*/

    private function extractPrice(TripDecomposition $trip, AircraftPricingCalculator $calculator): float
    {
        return $calculator->getProperties()->getPrice()->getAmount();
    }
}