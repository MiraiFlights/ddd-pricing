<?php declare(strict_types=1);

namespace ddd\pricing\services\leg;

use ddd\adapter\Trip\domain\aggregates\FlightDecomposition;
use ddd\pricing\entities\AircraftPricingCalculator;
use ddd\pricing\exceptions\AircraftPricingCalculationException;
use ddd\pricing\services\leg\conditions\FlightConditionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use yii\base\Component;
use yii\di\Instance;

final class FlightConditionsChecker extends Component implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var FlightConditionInterface[] */
    public array $conditions = [];

    public function __construct(LoggerInterface $logger, array $config = [])
    {
        parent::__construct($config);
        $this->setLogger($logger);
    }

    public function init()
    {
        foreach ($this->conditions as $key => $value)
            $this->conditions[$key] = Instance::ensure($this->conditions[$key], $value);

        parent::init();
    }

    public function checkCalculatorConditions(FlightDecomposition $flight, AircraftPricingCalculator $calculator): bool
    {
        $this->logger->info('Checking calculator {calculator}', [
            'method' => __METHOD__,
            'calculator' => $calculator->getProperties()->getName()->getValue(),
        ]);

        foreach ($calculator->getProperties()->getConditions() as $condition => $value) {
            if (!array_key_exists($condition, $this->conditions))
                throw new AircraftPricingCalculationException('Unknown condition: ' . $condition);

            if (!$this->conditions[$condition]->isSatisfiedBy($value, $flight)) {
                $this->logger->info('Calculator {calculator} declined', [
                    'method' => __METHOD__,
                    'calculator' => $calculator->getProperties()->getName()->getValue(),
                    'condition' => $condition . ' (' . var_export($value, true) . ')'
                ]);
                return false;
            }
        }

        $this->logger->info('Calculator {calculator} approved', [
            'method' => __METHOD__,
            'calculator' => $calculator->getProperties()->getName()->getValue(),
        ]);
        return true;
    }
}