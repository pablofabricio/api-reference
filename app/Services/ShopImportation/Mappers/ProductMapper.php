<?php

namespace App\Services\ShopImportation\Mappers;

use Illuminate\Support\Str;

class ProductMapper implements MapperInterface
{
    public static function map(array $data): array
    {
        $products = [];

        foreach ($data as $row) {
            if (isset($row[0]) && $row[0] === 'product_id') {
                continue;
            }

            $externalId = $row[0] ?: Str::slug($row[2]);
            $externalIdVariation = $row[1] ?: Str::slug(join('-', array_filter([$row[31] ?? '', $row[32] ?? ''])));

            $productData = [
                'product_id' => $externalId ?? null,
                'variation_id' => $externalIdVariation ?? null,
                'name' => $row[2] ?? null,
                'url' => $row[3] ?? null,
                'weight' => $row[4] ?? null,
                'depth' => $row[5] ?? null,
                'width' => $row[6] ?? null,
                'height' => $row[7] ?? null,
                'price' => isset($row[16]) ? (float) $row[16] : null,
                'stock' => $row[30] ?? null,
                'color' => $row[31] ?? null,
                'variation' => $row[32] ?? null,
                'variation_price' => isset($row[33]) ? (float) $row[33] : null,
                'sku' => $row[34] ?? null,
            ];

            if (!isset($products[$externalId])) {
                $products[$externalId] = $productData;
            }

            $products[$externalId]['variations'][$externalIdVariation] = $productData;
        }

        return $products;
    }
}
