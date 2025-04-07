<?php 

namespace App\Services\ShopImportation\DTO;

class ShopImportationDto
{
    private string $token;
    private string $resource;
    private string $from_id;
    private string $to_id;
    private string $request_id;

    public function __construct(string $token, string $resource, string $from_id, string $to_id,string $request_id)
    {
        $this->token = $token;
        $this->resource = $resource;
        $this->from_id = $from_id;
        $this->to_id = $to_id;
        $this->request_id = $request_id;
    }

    public static function fromArray(array $item): self
    {
        return new self(
            $item['token'],
            $item['resource'],
            $item['external_id'],
            $item['to_id'],
            $item['request_id'],
        );
    }

    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'resource' => $this->resource,
            'from_id' => $this->from_id,
            'to_id' => $this->to_id,
            'request_id' => $this->request_id,
        ];
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getFromId(): string
    {
        return $this->from_id;
    }

    public function getToId(): string
    {
        return $this->to_id;
    }
}
