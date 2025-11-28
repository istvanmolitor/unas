<?php

declare(strict_types=1);

namespace Molitor\Unas\Services\Endpoints;

use Molitor\Unas\Services\UnasService;

class GetStockEndpoint extends Endpoint
{
    protected function getEndpoint(): string
    {
        return 'shop/getStock';
    }

    protected function getRootTag(): string
    {
        return 'Product';
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

    public function getResultStock(): int
    {
        $products = $this->getResultProducts();
        if(count($products)) {
            return (int)$products[0]['Stocks']['Stock']['Qty'];
        }
        return 0;
    }
}
