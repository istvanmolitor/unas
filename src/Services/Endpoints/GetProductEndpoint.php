<?php

declare(strict_types=1);

namespace Molitor\Unas\Services\Endpoints;

class GetProductEndpoint extends Endpoint
{

    protected function getEndpoint(): string
    {
        return 'shop/getProduct';
    }

    public function setIdRequestData(int $id): void
    {
        $this->setRequestData([
            'Id' => $id,
            'ContentType' => 'full',
        ]);
    }

    public function setSkuRequestData(string $sku): void
    {
        $this->setRequestData([
            'Sku' => $sku,
            'ContentType' => 'full',
        ]);
    }

    protected function getRootTag(): string
    {
        return 'Params';
    }

    public function getResultProducts(): array
    {
        if (isset($this->result['Product'])) {
            if (isset($this->result['Product'][0])) {
                return $this->result['Product'];
            } else {
                return [$this->result['Product']];
            }
        }
        return [];
    }
}
