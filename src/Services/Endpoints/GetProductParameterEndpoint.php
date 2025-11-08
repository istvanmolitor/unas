<?php

declare(strict_types=1);

namespace Molitor\Unas\Services\Endpoints;

class GetProductParameterEndpoint extends Endpoint
{
    protected function getEndpoint(): string
    {
        return 'shop/getProductParameter';
    }

    protected function getRootTag(): string
    {
        return 'Params';
    }

    public function getResultProductParameters(): array
    {
        if (isset($this->result['ProductParameter'])) {
            if (isset($this->result['ProductParameter'][0])) {
                return $this->result['ProductParameter'];
            } else {
                return [$this->result['ProductParameter']];
            }
        }
        return [];
    }
}
