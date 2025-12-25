<?php declare(strict_types=1);

namespace ddd\pricing\services;

use ddd\adapter\Trip\domain\aggregates\TripDecomposition;
use ddd\pricing\entities\AircraftPricingCalculator;
use ddd\pricing\exceptions\AircraftPricingBadConfigurationException;
use ddd\pricing\interfaces\AircraftPricingHelperInterface;
use ddd\pricing\services\guards\TripCalculatorGuards;
use ddd\pricing\services\trip\TripUnitExtractor;
use ddd\pricing\values;
use ddd\pricing\values\AircraftPricingCalculatorType;
use unapi\helper\money\Wallet;

final class TripCalculatorService
{
    public function __construct(
        private readonly FlightCalculatorService               $flightCalculatorService,
        private readonly TripUnitExtractor                     $unitExtractor,
        private readonly FiltersChecker                        $filtersChecker,
        private readonly AircraftPricingCalculatorRoundService $roundService,
        private readonly AircraftPricingHelperInterface        $helper
    )
    {
    }

    /**
     * @param TripDecomposition $trip
     * @return DetailedWallet
     * @throws AircraftPricingBadConfigurationException
     */
    public function getPrice(TripDecomposition $trip): DetailedWallet
    {
        $details = ['flights' => [], 'trip' => [], 'tax' => [], 'datetime' => date('Y-m-d H:i:s')];
        $legsPrice = new Wallet();
        $taxable = new Wallet();

        $calculators = iterator_to_array($this->helper->getActualCalculators($trip), false);
        TripCalculatorGuards::guardAirwayTimeCalculatorExists($calculators);

        $legCalculators = $this->selectCalculators($calculators, new AircraftPricingCalculatorType(AircraftPricingCalculatorType::LEG));
        $legMarginCalculators = $this->selectCalculators($calculators, new AircraftPricingCalculatorType(AircraftPricingCalculatorType::LEG_MARGIN));
        $tripCalculators = $this->selectCalculators($calculators, new AircraftPricingCalculatorType(AircraftPricingCalculatorType::TRIP));
        $tripMarginCalculators = $this->selectCalculators($calculators, new AircraftPricingCalculatorType(AircraftPricingCalculatorType::TRIP_MARGIN));

        foreach ($trip->getFlights() as $flight) {
            $flightDetails = ['calculators' => []];
            $legPrice = new Wallet();
            $legTaxable = new Wallet();
            foreach ($legCalculators as $legCalculator) {
                $price = $this->flightCalculatorService->calculatePrice($flight, $legCalculator);
                if (null === $price)
                    continue;

                switch ($legCalculator->getProperties()->getTax()->getValue()) {
                    case values\AircraftPricingCalculatorTax::IS_TAXABLE:
                        $legTaxable->addMoney($price);
                        $taxable->addMoney($price);
                        break;
                    case values\AircraftPricingCalculatorTax::IS_TAX:
                        $details['tax'][] = (new DetailedWallet([$price]))->setDetails($legCalculator->jsonSerialize());
                        break;
                }

                $flightDetails['calculators'][] = (new DetailedWallet([$price]))->setDetails($legCalculator->jsonSerialize());
                $legPrice->addMoney($price);
            }

            $marginPrice = new Wallet();
            foreach ($legMarginCalculators as $legMarginCalculator) {
                $margin = $this->flightCalculatorService->calculateMargin($flight, $legMarginCalculator, $legTaxable);
                if (null === $margin)
                    continue;
                $marginPrice->addWallet($margin);
                $flightDetails['calculators'][] = DetailedWallet::fromWallet($margin)->setDetails($legMarginCalculator->jsonSerialize());
            }
            $legPrice->addWallet($marginPrice);

            $legsPrice->addWallet($legPrice);
            $details['flights'][] = DetailedWallet::fromWallet($legPrice)->setDetails($flightDetails);
        }

        $tripPrice = array_reduce(
            $tripCalculators,
            function (Wallet $initial, AircraftPricingCalculator $tripCalculator) use ($trip, &$details, &$taxable, &$tax) {
                if (!$this->checkCalculatorFilters($trip, $tripCalculator))
                    return $initial;

                $amount = $tripCalculator->getProperties()->getPrice()->multiply(
                    $this->roundService->applyRoundMethod(
                        $this->unitExtractor->extractUnit($trip, $tripCalculator->getProperties()->getUnit()->getValue()),
                        $tripCalculator->getProperties()->getRound()->getValue()
                    )
                );

                switch ($tripCalculator->getProperties()->getTax()->getValue()) {
                    case values\AircraftPricingCalculatorTax::IS_TAXABLE:
                        $taxable->addMoney($amount);
                        break;
                    case values\AircraftPricingCalculatorTax::IS_TAX:
                        $details['tax'][] = (new DetailedWallet([$amount]))->setDetails($tripCalculator->jsonSerialize());
                        break;
                }

                $details['trip'][] = (new DetailedWallet([$amount]))->setDetails($tripCalculator->jsonSerialize());
                return $initial->addMoney($amount);
            },
            new Wallet()
        );

        $taxPrice = array_reduce(
            $tripMarginCalculators,
            function (Wallet $initial, AircraftPricingCalculator $taxCalculator) use ($trip, &$details, $taxable) {
                $amount = $taxable->multiply($taxCalculator->getProperties()->getPercent() / 100);
                $details['tax'][] = DetailedWallet::fromWallet($amount)->setDetails($taxCalculator->jsonSerialize());
                return $initial->addWallet($amount);
            },
            new Wallet()
        );

        return (new DetailedWallet())
            ->addWallet($legsPrice)
            ->addWallet($tripPrice)
            ->addWallet($taxPrice)
            ->setDetails($details);
    }

    /**
     * @param AircraftPricingCalculator[] $calculators
     * @param AircraftPricingCalculatorType $calculatorType
     * @return AircraftPricingCalculator[]
     */
    private function selectCalculators(array $calculators, AircraftPricingCalculatorType $calculatorType): array
    {
        return array_filter(
            $calculators,
            fn(AircraftPricingCalculator $calculator) => $calculator->getProperties()->getType()->equalTo($calculatorType)
        );
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
}