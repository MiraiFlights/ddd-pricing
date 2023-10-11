<?php declare(strict_types=1);

namespace ddd\pricing\services;

use ddd\pricing\exceptions\AircraftPricingProfileNotFoundException;
use ddd\pricing\exceptions\AircraftPricingProfileSavingException;
use ddd\pricing\fabrics\AircraftPricingProfileFabric;
use ddd\pricing\fabrics\AircraftPricingProfileCalculatorFabric;
use ddd\pricing\entities\AircraftPricingCalculator;
use ddd\pricing\entities\AircraftPricingProfile;
use ddd\pricing\interfaces\AircraftPricingProfileProxyServiceInterface;
use ddd\pricing\values\AircraftPricingCalculatorProperties;
use ddd\pricing\values\AircraftPricingProfileID;
use ddd\pricing\values\AircraftPricingProfileName;
use ddd\pricing\values\AircraftPricingProfileProperties;
use GuzzleHttp\Client;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use rubin\adapter\guzzle\GuzzleClientFabricInterface;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;

final class AircraftPricingProfileProxyService extends Component implements AircraftPricingProfileProxyServiceInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public $serviceUrl;
    public $company_id;

    private Client $client;
    private AircraftPricingProfileFabric $profileFabric;
    private AircraftPricingProfileCalculatorFabric $profileCalculatorFabric;

    public function __construct(
        AircraftPricingProfileFabric           $profileFabric,
        AircraftPricingProfileCalculatorFabric $profileCalculatorFabric,
        GuzzleClientFabricInterface            $guzzleClientFabric,
        LoggerInterface                        $logger,
        array                                  $config = []
    )
    {
        $this->profileFabric = $profileFabric;
        $this->profileCalculatorFabric = $profileCalculatorFabric;
        parent::__construct($config);
        $this->setLogger($logger);
        $this->client = $guzzleClientFabric->build($this->serviceUrl, $logger);
    }

    public function getAvailableProfileNames(?AircraftPricingProfileName $name = null): array
    {
        try {
            $response = $this->client->request('get', '/profile/index', [
                'query' => [
                    'fields' => 'id,name',
                    'name' => $name ? $name->getValue() : null,
                    'company_id' => $this->company_id,
                ]
            ]);

            $response->getBody()->rewind();
            $data = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() == 422) {
                $error = reset($data);
                throw new HttpException(400, $error->message);
            }

            if ($response->getStatusCode() !== 200)
                throw new \RuntimeException('PRICING_SERVICE_ERROR');

            return ArrayHelper::map($data, 'id', 'name');
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            throw new AircraftPricingProfileNotFoundException($e->getMessage(), 0, $e);
        }
    }

    public function getCalculatorsByProfilesIds(array $ids = []): array
    {
        if (empty($ids))
            return [];

        try {
            $response = $this->client->request('get', '/profile/index', [
                'query' => [
                    'id' => $ids,
                    'company_id' => $this->company_id,
                ]
            ]);

            $response->getBody()->rewind();
            $data = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() == 422) {
                $error = reset($data);
                throw new HttpException(400, $error->message);
            }

            if ($response->getStatusCode() !== 200)
                throw new \RuntimeException('PRICING_SERVICE_ERROR');

            return $this->profileCalculatorFabric->fromArray(
                array_reduce($data, function ($carry, $profile) {
                    foreach (ArrayHelper::getValue($profile, 'calculators') as $calculator) {
                        $carry[] = $calculator;
                    }
                    return $carry;
                }, [])
            );
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            throw new AircraftPricingProfileNotFoundException($e->getMessage(), 0, $e);
        }
    }

    public function createProfile(AircraftPricingProfileProperties $properties): AircraftPricingProfile
    {
        try {
            $response = $this->client->request('post', '/profile/create', [
                'query' => [
                    'company_id' => $this->company_id,
                ],
                'json' => [
                    'name' => $properties->getName()->getValue(),
                    'company_id' => $this->company_id,
                ]
            ]);

            $response->getBody()->rewind();
            $data = json_decode($response->getBody()->getContents());

            if ($response->getStatusCode() == 422) {
                $error = reset($data);
                throw new HttpException(400, $error->message);
            }

            if ($response->getStatusCode() !== 201)
                throw new \RuntimeException('PRICING_SERVICE_ERROR');

            return $this->profileFabric->fromData($data);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            throw new AircraftPricingProfileSavingException($e->getMessage(), 0, $e);
        }
    }

    public function getProfile(AircraftPricingProfileID $id): AircraftPricingProfile
    {
        try {
            $response = $this->client->request('get', '/profile/view', [
                'query' => [
                    'id' => $id->getValue(),
                ]
            ]);

            $response->getBody()->rewind();
            $data = json_decode($response->getBody()->getContents());

            if ($response->getStatusCode() !== 200)
                throw new \RuntimeException('PRICING_SERVICE_ERROR');

            return $this->profileFabric->fromData($data);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            throw new AircraftPricingProfileNotFoundException($e->getMessage(), 0, $e);
        }
    }

    public function createProfileCalculator(AircraftPricingCalculatorProperties $properties): AircraftPricingCalculator
    {
        try {
            $response = $this->client->request('post', '/profile-calculator/create', [
                'query' => [
                    'company_id' => $this->company_id,
                ],
                'json' => [
                    'name' => $properties->getName()->getValue(),
                    'price' => $properties->getPrice()->getAmount(),
                    'pricing_profile_id' => $properties->getAircraftPricingProfileID()->getValue(),
                    'type_id' => $properties->getType()->getValue(),
                    'settings' => [
                        'conditions' => $properties->getConditions(),
                        'filters' => $properties->getFilters(),
                        'unit' => $properties->getUnit()->getValue(),
                        'tax' => $properties->getTax()->getValue(),
                        'round' => $properties->getRound()->getValue(),
                    ],
                ]
            ]);

            $response->getBody()->rewind();
            $data = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() == 422) {
                $error = reset($data);
                throw new HttpException(400, $error->message);
            }

            if ($response->getStatusCode() !== 201)
                throw new \RuntimeException('PRICING_SERVICE_ERROR');

            return $this->profileCalculatorFabric->fromData($data);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            throw new AircraftPricingProfileSavingException($e->getMessage(), 0, $e);
        }
    }
}