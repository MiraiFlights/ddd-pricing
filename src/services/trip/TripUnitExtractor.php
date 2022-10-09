<?php declare(strict_types=1);

namespace ddd\pricing\services\trip;

use ddd\adapter\Trip\domain\aggregates\TripDecomposition;
use ddd\pricing\exceptions\AircraftPricingCalculationException;
use ddd\pricing\services\trip\extractors\TripExtractorInterface;
use yii\base\Component;
use yii\di\Instance;

final class TripUnitExtractor extends Component
{
    /** @var TripExtractorInterface[] */
    public array $extractors = [];

    public function init()
    {
        foreach ($this->extractors as $key => $value)
            $this->extractors[$key] = Instance::ensure($this->extractors[$key], $value);

        parent::init();
    }

    public function extractUnit(TripDecomposition $trip, string $unit): float
    {
        if (!array_key_exists($unit, $this->extractors))
            throw new AircraftPricingCalculationException('Unknown extractor: ' . $unit);

        return $this->extractors[$unit]->extractValue($trip);
    }
}