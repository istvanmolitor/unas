<?php

declare(strict_types=1);

namespace Molitor\Unas\Services\Endpoints;

abstract class Endpoint extends Client
{
    const ACTION_UPDATE = 'modify';
    const ACTION_INSERT = 'add';
    const ACTION_DELETE = 'delete';

    private Auth $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
        parent::__construct($this->getEndpoint(), $this->getRootTag());
    }

    public function getHeader(): array
    {
        return [
            "Authorization: Bearer " . $this->auth->getToken()
        ];
    }

    abstract protected function getEndpoint(): string;

    abstract protected function getRootTag(): string;

    public function execute(): void
    {
        if (!$this->auth->isExecuted()) {
            $this->auth->execute();
        }
        $this->setHeader($this->getHeader());
        parent::execute();
        $this->processing();
    }

    protected function processing(): void
    {

    }
}
