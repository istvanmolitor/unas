<?php

declare(strict_types=1);

namespace Molitor\Unas\Services\Endpoints;

class SetProductEndpoint extends Endpoint
{
    protected function getEndpoint(): string
    {
        return 'shop/setProduct';
    }

    protected function getRootTag(): string
    {
        return 'Products';
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
