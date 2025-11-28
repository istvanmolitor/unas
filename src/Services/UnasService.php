<?php

namespace Molitor\Unas\Services;

use Molitor\Unas\Services\Endpoints\Auth;
use Molitor\Unas\Services\Endpoints\GetCategoryEndpoint;
use Molitor\Unas\Services\Endpoints\GetOrderEndpoint;
use Molitor\Unas\Services\Endpoints\GetProductEndpoint;
use Molitor\Unas\Services\Endpoints\GetProductParameterEndpoint;
use Molitor\Unas\Services\Endpoints\GetStockEndpoint;
use Molitor\Unas\Services\Endpoints\SetCategoryEndpoint;
use Molitor\Unas\Services\Endpoints\SetProductEndpoint;
use Molitor\Unas\Services\Endpoints\SetProductParameterEndpoint;

class UnasService
{
    const STATUS_OK = 'ok';
    const ACTION_UPDATE = 'modify';
    const ACTION_CREATE = 'add';
    const ACTION_DELETE = 'delete';

    private static array $auths = [];

    public function getAuth(string $apiKey): Auth
    {
        if (!array_key_exists($apiKey, self::$auths)) {
            self::$auths[$apiKey] = new Auth($apiKey);
        }
        return self::$auths[$apiKey];
    }

    public function makeGetCategoryEndpoint(string $apiKey): GetCategoryEndpoint
    {
        return new GetCategoryEndpoint($this->getAuth($apiKey));
    }

    public function makeSetCategoryEndpoint(string $apiKey): SetCategoryEndpoint
    {
        return new SetCategoryEndpoint($this->getAuth($apiKey));
    }

    public function makeGetProductEndpoint(string $apiKey): GetProductEndpoint
    {
        return new GetProductEndpoint($this->getAuth($apiKey));
    }

    public function makeSetProductEndpoint(string $apiKey): SetProductEndpoint
    {
        return new SetProductEndpoint($this->getAuth($apiKey));
    }

    public function makeGetProductParameterEndpoint(string $apiKey): GetProductParameterEndpoint
    {
        return new GetProductParameterEndpoint($this->getAuth($apiKey));
    }

    public function makeSetProductParameterEndpoint(string $apiKey): SetProductParameterEndpoint
    {
        return new SetProductParameterEndpoint($this->getAuth($apiKey));
    }

    public function makeGetOrderEndpoint(string $apiKey): GetOrderEndpoint
    {
        return new GetOrderEndpoint($this->getAuth($apiKey));
    }

    public function makeGetStockEndpoint(string $apiKey): GetStockEndpoint
    {
        return new GetStockEndpoint($this->getAuth($apiKey));
    }

    public function getBooleanString(bool $value): string
    {
        return $value ? 'yes' : 'no';
    }
}
