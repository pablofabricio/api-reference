<?php

namespace App\Services\ShopImportation;

use App\Repositories\ShopImportationLogRepository;

class ShopImportationLogger
{
    private ShopImportationLogRepository $logRepository;

    public function __construct(ShopImportationLogRepository $logRepository)
    {
        $this->logRepository = $logRepository;
    }

    public function addError(string $requestId, array $error): void
    {
        $log = $this->logRepository->findByRequestId($requestId);

        $errors = $log->errors ?? [];
        $errors[] = $error;

        $this->logRepository->update($requestId, ['errors' => json_encode($errors)]);
    }

    public function updateStatus(string $requestId, string $status): void
    {
        $this->logRepository->update($requestId, ['status' => $status]);
    }
}
