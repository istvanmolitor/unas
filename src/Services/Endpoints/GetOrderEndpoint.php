<?php

namespace Molitor\Unas\Services\Endpoints;

class GetOrderEndpoint extends Endpoint
{

    protected function getEndpoint(): string
    {
        return 'shop/getOrder';
    }

    protected function getRootTag(): string
    {
        return 'Orders';
    }

    public function getResultOrders(): array
    {
        if (isset($this->result['Order'])) {
            if (isset($this->result['Order'][0])) {
                return $this->result['Order'];
            } else {
                return [$this->result['Order']];
            }
        }
        return [];
    }
}
