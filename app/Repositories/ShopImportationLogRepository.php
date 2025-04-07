<?php

namespace App\Repositories;

use App\Models\ShopImportationLog;

class ShopImportationLogRepository
{
    public function findByRequestId(string $requestId): ?ShopImportationLog
    {
        return ShopImportationLog::where('request_id', $requestId)->first();
    }

    public function insert(array $data): ShopImportationLog
    {
        return ShopImportationLog::create($data);
    }

    public function update(string $requestId, array $data): void
    {
        ShopImportationLog::where('request_id', $requestId)->update($data);
    }

    public function updateStatus(string $requestId, string $status): void
    {
        ShopImportationLog::where('request_id', $requestId)
            ->update(['status' => $status]);
    }
}
