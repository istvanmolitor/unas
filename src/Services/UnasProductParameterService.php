<?php

namespace Molitor\Unas\Services;

use Molitor\Language\Repositories\LanguageRepositoryInterface;
use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Models\UnasProductParameter;
use Molitor\Unas\Repositories\UnasProductParameterRepositoryInterface;

class UnasProductParameterService extends UnasService
{
    public function __construct(
        private UnasProductParameterRepositoryInterface $unasProductParameterRepository,
        private LanguageRepositoryInterface             $languageRepository
    )
    {
    }

    public function repairParameters(UnasShop $shop): void
    {
        $this->unasProductParameterRepository->forceDeleteByShop($shop);
        $this->downloadParameters($shop);
    }

    public function getByResult(UnasShop $shop, array $resultProductParameter): UnasProductParameter|null
    {
        $paremeter = $this->unasProductParameterRepository->getByRemoteId($shop, (int)$resultProductParameter['Id']);
        if($paremeter) {
            return $paremeter;
        }

        $language = $this->languageRepository->getByCode($resultProductParameter['Lang']);
        $paremeter = $this->unasProductParameterRepository->getByName(
            $shop,
            $resultProductParameter['Name'],
            $language
        );
        if ($paremeter) {
            if($paremeter->remote_id === null) {
                $paremeter->remote_id = (int)$resultProductParameter['Id'];
                $paremeter->save();
            }
            return $paremeter;
        }

        return UnasProductParameter::create(
            [
                'unas_shop_id' => $shop->id,
                'name' => $resultProductParameter['Name'],
                'type' => $resultProductParameter['Type'],
                'language_id' => $language->id,
                'order' => (int)$resultProductParameter['Order'],
                'remote_id' => (int)$resultProductParameter['Id'],
            ]
        );
    }

    public function downloadParameters(UnasShop $shop): void
    {
        $endpoint = $this->makeGetProductParameterEndpoint($shop->api_key);
        $endpoint->execute();

        foreach ($endpoint->getResultProductParameters() as $resultProductParameter) {
            $parameter = $this->getByResult($shop, $resultProductParameter);
            $parameter->name = $resultProductParameter['Name'];
            $parameter->type = $resultProductParameter['Type'];
            $parameter->order = (int)$resultProductParameter['Order'];
            $parameter->save();
        }
    }

    public function clearShop(UnasShop $shop): void
    {
    }

    public function syncChanges(UnasShop $shop): bool
    {
        $shopProductParameters = $shop->shopProductParameters()
            ->with('productField')
            ->where('unas_product_parameters.changed', 1)
            ->get();

        if ($shopProductParameters->count() == 0) {
            return false;
        }

        $endpoint = $this->makeSetProductParameterEndpoint($shop->api_key);

        $requestData = [];

        foreach ($shopProductParameters as $shopProductParameter) {
            $requestProductParameter = [
                'Name' => $shopProductParameter->productField->name,
                'Display' => [
                    'ProductDetails' => 'yes'
                ],
            ];

            if ($shopProductParameter->remote_id) {
                $requestProductParameter['Action'] = self::ACTION_UPDATE;
                $requestProductParameter['Id'] = $shopProductParameter->remote_id;
            } else {
                $requestProductParameter['Action'] = self::ACTION_CREATE;
                $requestProductParameter['Type'] = $shopProductParameter->type;
                $requestProductParameter['Lang'] = $shopProductParameter->productField->language->code;
            }

            $requestData['@ProductParameter'][] = $requestProductParameter;
        }

        $endpoint->setRequestData($requestData);
        $endpoint->execute();

        $resultProductParameters = $endpoint->getResultProductParameters();

        foreach ($shopProductParameters as $i => $shopProductParameter) {
            if (!isset($resultProductParameters[$i])) {
                return false;
            }
            $resultProductParameter = $resultProductParameters[$i];
            if ($resultProductParameter['Status'] == self::STATUS_OK) {
                if ($resultProductParameter['Action'] == self::ACTION_CREATE) {
                    $shopProductParameter->remote_id = $resultProductParameter['Id'];
                }
                $shopProductParameter->changed = 0;
                $shopProductParameter->save();
            } else {
                return false;
            }
        }

        return true;
    }

    public function syncDeletes(UnasShop $shop): int
    {
        $shopProductParameters = $shop->shopProductParameters()->onlyTrashed()->get();
        if ($shopProductParameters->count() == 0) {
            return 0;
        }

        $endpoint = $this->makeSetProductParameterEndpoint($shop->api_key);

        $requestData = [];
        foreach ($shopProductParameters as $shopProductParameter) {
            $requestData['@ProductParameter'][] = [
                'Action' => self::ACTION_DELETE,
                'Id' => $shopProductParameter->remote_id,
            ];
        }

        $endpoint->setRequestData($requestData);
        $endpoint->execute();

        $i = 0;

        foreach ($endpoint->getResultProductParameters() as $resultProductParameter) {
            if ($resultProductParameter['Status'] == self::STATUS_OK) {
                $i++;
                $this->unasProductParameterRepository->forceDeleteByRemoteId($resultProductParameter['Id']);
            }
        }
        return $i;
    }
}
