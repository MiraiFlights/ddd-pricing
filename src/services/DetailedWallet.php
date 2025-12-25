<?php declare(strict_types=1);

namespace ddd\pricing\services;

use unapi\helper\money\MoneyAmount;
use unapi\helper\money\Wallet;

final class DetailedWallet extends Wallet implements \JsonSerializable
{
    private array $details = [];

    /**
     * @param Wallet $wallet
     * @return static
     */
    public static function fromWallet(Wallet $wallet): self
    {
        return new static($wallet->getMoney());
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
     * @return static
     */
    public function setDetails(array $details): static
    {
        $this->details = $details;
        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return static
     */
    public function addDetail(string $key, $value): static
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

    public function jsonSerialize(): mixed
    {
        return [
            'money' => array_map(
                fn(MoneyAmount $money) => [
                    'amount' => $money->getAmount(),
                    'currency' => $money->getCurrency(),
                ],
                $this->getMoney()
            ),
            'details' => $this->details,
        ];
    }
}