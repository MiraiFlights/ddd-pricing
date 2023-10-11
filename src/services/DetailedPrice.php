<?php declare(strict_types=1);

namespace ddd\pricing\services;

use unapi\helper\money\MoneyAmount;

final class DetailedPrice extends MoneyAmount implements \JsonSerializable
{
    private array $details = [];

    /**
     * @param MoneyAmount $moneyAmount
     * @return static
     */
    public static function fromMoneyAmount(MoneyAmount $moneyAmount): self
    {
        return new static($moneyAmount->getAmount(), $moneyAmount->getCurrency());
    }

    /**
     * @return array
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * @param array $details
     * @return DetailedPrice
     */
    public function setDetails(array $details): DetailedPrice
    {
        $this->details = $details;
        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return DetailedPrice
     */
    public function addDetail(string $key, $value): DetailedPrice
    {
        $this->details[$key] = $value;
        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getDetail(string $key)
    {
        return $this->details[$key] ?? null;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize ()
    {
        return [
            'amount' => $this->getAmount(),
            'currency' => $this->getCurrency(),
            'details' => $this->details,
        ];
    }
}