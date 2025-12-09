<?php

namespace Molitor\Unas\Services\Endpoints;

class GetOrderStatusEndpoint extends Endpoint
{

    protected function getEndpoint(): string
    {
        return 'shop/getOrderStatus';
    }

    protected function getRootTag(): string
    {
        return 'OrderStatuses';
    }
}
