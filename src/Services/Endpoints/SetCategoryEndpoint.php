<?php

declare(strict_types=1);

namespace Molitor\Unas\Services\Endpoints;

class SetCategoryEndpoint extends Endpoint
{
    protected function getEndpoint(): string
    {
        return 'shop/setCategory';
    }

    protected function getRootTag(): string
    {
        return 'Categories';
    }

    public function getResultCategories(): array
    {
        if (isset($this->result['Category'])) {
            if (isset($this->result['Category'][0])) {
                return $this->result['Category'];
            } else {
                return [$this->result['Category']];
            }
        }
        return [];
    }
}
