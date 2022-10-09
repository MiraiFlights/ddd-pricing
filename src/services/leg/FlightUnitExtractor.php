<?php declare(strict_types=1);

namespace ddd\pricing\services\leg;

use ddd\adapter\Trip\domain\aggregates\FlightDecomposition;
use ddd\pricing\exceptions\AircraftPricingCalculationException;
use ddd\pricing\services\leg\extractors\FlightExtractorInterface;
use yii\base\Component;
use yii\di\Instance;

final class FlightUnitExtractor extends Component
{
    /** @var FlightExtractorInterface[] */
    public array $extractors = [];

    public function init()
    {
        foreach ($this->extractors as $key => $value)
            $this->extractors[$key] = Instance::ensure($this->extractors[$key], $value);

        parent::init();
    }

    public function extractUnit(FlightDecomposition $flight, string $unit): float
    {
        if (!array_key_exists($unit, $this->extractors))
            throw new AircraftPricingCalculationException('Unknown extractor: ' . $unit);

        return $this->extractors[$unit]->extractValue($flight);
    }
}