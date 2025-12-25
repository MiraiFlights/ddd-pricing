<?php declare(strict_types=1);

namespace ddd\pricing\fabrics;

use ddd\pricing\entities;
use ddd\pricing\mappers\AircraftPricingProfileCalculatorTypeMapper;
use ddd\pricing\values;
use unapi\helper\money\MoneyAmount;
use yii\helpers\ArrayHelper;

final class AircraftPricingProfileCalculatorFabric
{
    /**
     * @param array $data
     * @return entities\AircraftPricingCalculator[]
     */
    public function fromArray(array $data): array
    {
        return array_map(
            fn($item) => $this->fromData($item),
            $data
        );
    }

    /**
     * @param mixed $data
     * @return entities\AircraftPricingCalculator
     */
    public function fromData($data): entities\AircraftPricingCalculator
    {
        $type = values\AircraftPricingCalculatorType::fromValue(AircraftPricingProfileCalculatorTypeMapper::webToCore(ArrayHelper::getValue($data, 'type')));
        $properties = (new values\AircraftPricingCalculatorProperties(
            $type,
            new values\AircraftPricingCalculatorName((string)ArrayHelper::getValue($data, 'name')),
            ArrayHelper::getValue($data, 'settings.conditions', []),
            ArrayHelper::getValue($data, 'settings.filters', []),
            values\AircraftPricingCalculatorUnit::fromValue(ArrayHelper::getValue($data, 'settings.unit')),
            values\AircraftPricingCalculatorTax::fromValue(ArrayHelper::getValue($data, 'settings.tax', values\AircraftPricingCalculatorTax::NONE)),
            values\AircraftPricingCalculatorRound::fromValue(ArrayHelper::getValue($data, 'settings.round', values\AircraftPricingCalculatorRound::NONE))
        ))
            ->setAircraftPricingProfileID(new values\AircraftPricingProfileID(ArrayHelper::getValue($data, 'pricing_profile_id')));

       if (in_array($type->getValue(), [values\AircraftPricingCalculatorType::LEG, values\AircraftPricingCalculatorType::TRIP])) {
            $properties->setPrice(new MoneyAmount((float)ArrayHelper::getValue($data, 'price'), ArrayHelper::getValue($data, 'currency')));
        } else {
           $properties->setPercent(ArrayHelper::getValue($data, 'percent'));
       }

        return entities\AircraftPricingCalculator::load(
            new values\AircraftPricingCalculatorID(ArrayHelper::getValue($data, 'id')),
            $properties
        );
    }
}