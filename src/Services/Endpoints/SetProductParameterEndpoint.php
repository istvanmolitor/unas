<?php

declare(strict_types=1);

namespace Molitor\Unas\Services\Endpoints;

class SetProductParameterEndpoint extends Endpoint
{
    protected function getEndpoint(): string
    {
        return 'shop/setProductParameter';
    }

    protected function getRootTag(): string
    {
        return 'ProductParameters';
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
