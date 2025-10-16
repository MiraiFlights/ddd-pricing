<?php declare(strict_types=1);

namespace ddd\pricing\services;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use Yii;

trait AircraftPricingFormatterTrait
{
    public function asCalculatorConditions(?array $conditions = []): string
    {
        if (empty($conditions))
            return $this->nullDisplay;

        $result = '';
        foreach ($conditions as $name => $value) {
            $inverse = ArrayHelper::getValue($value, 'inverse', false);
            switch ($name) {
                case 'departure_city_id':
                    $label = Yii::t('pricing', 'Departure City');
                    $text = implode(', ', array_map([$this, 'asCity'], $value['array']));
                    break;
                case 'departure_country_iso3':
                    $label = Yii::t('pricing', 'Departure Country');
                    $text = implode(', ', array_map([$this, 'asCountry'], $value['array']));
                    break;
                case 'departure_icao':
                    $label = Yii::t('pricing', 'Departure Airport');
                    $text = implode(', ', array_map([$this, 'asAirport'], $value['array']));
                    break;
                case 'arrival_city_id':
                    $label = Yii::t('pricing', 'Arrival City');
                    $text = implode(', ', array_map([$this, 'asCity'], $value['array']));
                    break;
                case 'arrival_country_iso3':
                    $label = Yii::t('pricing', 'Arrival Country');
                    $text = implode(', ', array_map([$this, 'asCountry'], $value['array']));
                    break;
                case 'arrival_icao':
                    $label = Yii::t('pricing', 'Arrival Airport');
                    $text = implode(', ', array_map([$this, 'asAirport'], $value['array']));
                    break;
                case 'cross_country_iso3':
                    $label = Yii::t('pricing', 'Cross Country');
                    $text = implode(', ', array_map([$this, 'asCountry'], $value['array']));
                    break;
                case 'inner':
                    $label = Yii::t('pricing', 'Inner');
                    $text = $value ? Yii::t('pricing', 'Yes') : Yii::t('pricing', 'No');
                    break;
                case 'ferry':
                    $label = Yii::t('pricing', 'Ferry');
                    $text = $value ? Yii::t('pricing', 'Yes') : Yii::t('pricing', 'No');
                    break;
                case 'holidays':
                    $label = Yii::t('pricing', 'Weekends');
                    $text = $value ? Yii::t('pricing', 'Yes') : Yii::t('pricing', 'No');
                    break;
                case 'flags':
                    $label = Yii::t('pricing', 'Flags');
                    $text = implode(', ', $value['array']);
                    break;
                case 'aircraft_type_id':
                    $label = Yii::t('pricing', 'Aircraft Type');
                    $text = implode(', ', array_map([$this, 'asAircraftType'], $value['array']));
                    break;
                case 'aircraft_class_id':
                    $label = Yii::t('pricing', 'Aircraft Class');
                    $text = implode(', ', array_map([$this, 'asAircraftClass'], $value['array']));
                    break;
                default:
                    $label = ucfirst($name);
                    $text = $value;
            }

            $result .= Html::tag('span', $label . ': ' . $text, ['class' => $inverse ? 'badge alert-danger' : 'badge alert-success']);
        }

        return $result;
    }

    public function asCalculatorFilters(?array $filters = []): string
    {
        if (empty($filters))
            return $this->nullDisplay;

        $result = '';
        foreach ($filters as $filter) {
            $result .= Html::tag('span', ucfirst(ArrayHelper::getValue($filter, 'unit', '')) . ' ' . ArrayHelper::getValue($filter, 'comparison', '') . ' ' . ArrayHelper::getValue($filter, 'value', ''), ['class' => 'badge']);
        }
        return $result;
    }
}