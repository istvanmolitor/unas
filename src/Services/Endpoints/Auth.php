<?php

declare(strict_types=1);

namespace Molitor\Unas\Services\Endpoints;

class Auth extends Client
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        parent::__construct('shop/login', 'Params');
        $this->setRequestData([
            'ApiKey' => $this->apiKey
        ]);
    }

    public function getToken()
    {
        return $this->result['Token'] ?? null;
    }
}
