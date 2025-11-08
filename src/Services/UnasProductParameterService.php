<?php

namespace Molitor\Unas\Services;

use Molitor\Language\Repositories\LanguageRepositoryInterface;
use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Models\UnasProductParameter;
use Molitor\Unas\Repositories\UnasProductParameterRepositoryInterface;

class UnasProductParameterService extends UnasService
{
    public function __construct(
        private UnasProductParameterRepositoryInterface $unasShopProductParameter,
        private LanguageRepositoryInterface             $languageRepository
    )
    {
    }

    public function repairParameters(UnasShop $shop): void
    {
        $this->unasShopProductParameter->forceDeleteByShop($shop);

        $endpoint = $this->makeGetProductParameterEndpoint($shop->api_key);
        $endpoint->execute();

        foreach ($endpoint->getResultProductParameters() as $resultProductParameter) {
            UnasProductParameter::create(
                [
                    'unas_shop_id' => $shop->id,
                    'name' => $resultProductParameter['Name'],
                    'type' => $resultProductParameter['Type'],
                    'language_id' => $this->languageRepository->getByCode($resultProductParameter['Lang'])->id,
                    'order' => (int)$resultProductParameter['Order'],
                    'remote_id' => (int)$resultProductParameter['Id'],
                ]
            );
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
                $this->unasShopProductParameter->forceDeleteByRemoteId($resultProductParameter['Id']);
            }
        }
        return $i;
    }
}