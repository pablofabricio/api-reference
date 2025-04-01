<?php

namespace App\Services\ShopImportation\Mappers;

use Generator;
use Illuminate\Support\Str;

class ProductMapper implements MapperInterface
{
    public static function map(array $row): array
    {
        $externalId = $row[0] ?: Str::slug($row[2]);
        $externalIdVariation = $row[1] ?: Str::slug(join('-', array_filter([$row[31] ?? '', $row[32] ?? ''])));

        return [
            'product_id' => $externalId ?? null,
            'variation_id' => $externalIdVariation ?? null,
            'name' => $row[2] ?? null,
            'url' => $row[3] ?? null,
            'weight' => $row[4] ?? null,
            'depth' => $row[5] ?? null,
            'width' => $row[6] ?? null,
            'height' => $row[7] ?? null,
            'meta_title' => $row[8] ?? null,
            'meta_description' => $row[9] ?? null,
            'meta_keywords' => $row[10] ?? null,
            'tags' => $row[11] ?? null,
            'description' => $row[12] ?? null,
            'short_description' => $row[13] ?? null,
            'ncm' => $row[14] ?? null,
            'images' => $row[15] ?? null,
            'price' => isset($row[16]) ? (float) $row[16] : null,
            'price_compare' => $row[17] ?? null,
            'model' => $row[18] ?? null,
            'gender' => $row[19] ?? null,
            'age_group' => $row[20] ?? null,
            'warranty' => $row[21] ?? null,
            'active' => $row[22] ?? null,
            'brand' => $row[23] ?? null,
            'category_1' => $row[24] ?? null,
            'category_2' => $row[25] ?? null,
            'category_3' => $row[26] ?? null,
            'reference' => $row[27] ?? null,
            'gtin' => $row[28] ?? null,
            'mpn' => $row[29] ?? null,
            'stock' => $row[30] ?? null,
            'color' => $row[31] ?? null,
            'variation' => $row[32] ?? null,
            'variation_price' => isset($row[33]) ? (float) $row[33] : null,
            'sku' => $row[34] ?? null,
        ];
    }
}
