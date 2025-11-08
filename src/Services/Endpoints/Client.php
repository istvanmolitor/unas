<?php

declare(strict_types=1);

namespace Molitor\Unas\Services\Endpoints;

use SimpleXMLElement;

class Client
{
    protected string $endpoint;
    protected string $rootTag;
    protected array $requestData = [];
    protected array $header = [];

    protected bool $executed = false;
    protected ?array $result = null;
    protected ?string $error = null;

    public function __construct(string $endpoint, string $rootTag)
    {
        $this->endpoint = $endpoint;
        $this->rootTag = $rootTag;
    }

    public function setHeader(array $header): self
    {
        $this->header = $header;
        return $this;
    }

    public function setRequestData(array $requestData): self
    {
        $this->requestData = $requestData;
        return $this;
    }

    private function buildXml(array $data, SimpleXMLElement &$xmlData): void
    {
        foreach ($data as $key => $value) {
            if (substr($key, 0, 1) == '@') {
                foreach ($value as $subvalue) {
                    $subnode = $xmlData->addChild(substr($key, 1));
                    $this->buildXml($subvalue, $subnode);
                }
            } else {
                if (is_array($value)) {
                    if (is_numeric($key)) {
                        $key = 'item' . $key; //dealing with <0/>..<n/> issues
                    }
                    $subnode = $xmlData->addChild($key);
                    $this->buildXml($value, $subnode);
                } else {
                    $xmlData->addChild((string)$key, htmlspecialchars((string)$value));
                }
            }
        }
    }

    public function getRequestXml(): string
    {
        $xmlData = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><' . $this->rootTag . '/>');
        $this->buildXml($this->requestData, $xmlData);
        return (string)$xmlData->asXML();
    }

    public function getUrl(): string
    {
        return 'https://api.unas.eu/' . $this->endpoint;
    }

    public function execute(): void
    {
        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_URL, $this->getUrl());

            $xml = $this->getRequestXml();

            curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                $this->result;
                $this->error = curl_error($curl);
            } else {
                $this->result = $this->emptyNodesToNull(json_decode(json_encode(simplexml_load_string($response, null, LIBXML_NOCDATA)), true));
                $this->error = null;
            }

            $this->log($xml, $this->result);
        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
        }
        $this->executed = true;
    }

    public function emptyNodesToNull($data)
    {
        if (is_array($data)) {
            if (count($data)) {
                foreach ($data as $key => $value) {
                    $data[$key] = $this->emptyNodesToNull($value);
                }
            } else {
                return null;
            }
        }
        return $data;
    }

    public function log($request, $result): self
    {
        return $this;
    }

    public function getResult(): array|null
    {
        return $this->result;
    }

    public function getError(): string|null
    {
        return $this->error;
    }

    public function isExecuted(): bool
    {
        return $this->executed;
    }
}
