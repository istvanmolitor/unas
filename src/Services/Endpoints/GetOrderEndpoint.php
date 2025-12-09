<?php

namespace Molitor\Unas\Services\Endpoints;

use Carbon\Carbon;

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

    public function setKeyRequestData(string $key): void
    {
        $this->setRequestData([
            'Key' => $key,
        ]);
    }

    public function setDateRequestData(Carbon $date): void
    {
        $date->setTimezone('Europe/Budapest');
        $date = $date->format('Y.m.d');

        $this->setRequestData([
            'DateStart' => $date,
            'DateEnd' => $date,
        ]);
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

    public function getCustomerByOrderResult(array $orderResult): array
    {
        return [
            'mail' => $orderResult['Customer']['Email'] ?? null,
            'username' => $orderResult['Customer']['Username'] ?? null,
            'phone' => $orderResult['Customer']['Phone'] ?? null,
            'lang' => $orderResult['Customer']['Lang'] ?? null,
        ];
    }
}
