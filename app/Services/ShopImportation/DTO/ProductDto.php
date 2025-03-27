<?php 

namespace App\Services\ShopImportation\DTO;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Color;
use App\Models\AttributeValue;

class ProductDto
{
    public string $name;
    public string $externalId;
    public ?int $brandId;
    public array $categoryIds;
    public ?string $description;
    public ?string $shortDescription;
    public ?string $metaTitle;
    public ?string $metaDescription;
    public ?string $metaKeywords;
    public ?string $tags;
    public ?float $weight;
    public ?float $depth;
    public ?float $width;
    public ?float $height;
    public ?string $ncm;
    public float $price;
    public ?float $priceCompare;
    public ?string $warranty;
    public ?string $model;
    public ?string $gender;
    public ?string $ageGroup;
    public array $variations;

    public function __construct(
        string $name,
        string $externalId,
        ?int $brandId,
        array $categoryIds,
        ?string $description,
        ?string $shortDescription,
        ?string $metaTitle,
        ?string $metaDescription,
        ?string $metaKeywords,
        ?string $tags,
        ?float $weight,
        ?float $depth,
        ?float $width,
        ?float $height,
        ?string $ncm,
        float $price,
        ?float $priceCompare,
        ?string $warranty,
        ?string $model,
        ?string $gender,
        ?string $ageGroup,
        array $variations
    ) {
        $this->name = $name;
        $this->externalId = $externalId;
        $this->brandId = $brandId;
        $this->categoryIds = $categoryIds;
        $this->description = $description;
        $this->shortDescription = $shortDescription;
        $this->metaTitle = $metaTitle;
        $this->metaDescription = $metaDescription;
        $this->metaKeywords = $metaKeywords;
        $this->tags = $tags;
        $this->weight = $weight;
        $this->depth = $depth;
        $this->width = $width;
        $this->height = $height;
        $this->ncm = $ncm;
        $this->price = $price;
        $this->priceCompare = $priceCompare;
        $this->warranty = $warranty;
        $this->model = $model;
        $this->gender = $gender;
        $this->ageGroup = $ageGroup;
        $this->variations = $variations;
    }

    public static function fromArray(array $item)//: self
    {
        return ;
        //$categoryIds = array_filter([
        //    isset($item['category_1']) ? Category::getByPartnerId($item['category_1'])->dooca_id : null,
        //    isset($item['category_2']) ? Category::getByPartnerId($item['category_2'])->dooca_id : null,
        //    isset($item['category_3']) ? Category::getByPartnerId($item['category_3'])->dooca_id : null,
        //]);
//
        //$brandId = isset($item['brand']) ? Brand::getByPartnerId($item['brand'])->dooca_id : null;
        //
        //$variations = array_map(fn($value) => VariationDto::fromArray($value, $formatter), $item['variations'] ?? []);
//
        //return new self(
        //    $item['name'],
        //    $item['product_id'],
        //    $brandId,
        //    $categoryIds,
        //    $formatter->description($item['description'] ?? null),
        //    $formatter->description($item['short_description'] ?? null),
        //    $item['meta_title'] ?? null,
        //    $item['meta_description'] ?? null,
        //    $item['meta_keywords'] ?? null,
        //    $item['tags'] ?? null,
        //    isset($item['weight']) ? (float) str_replace(',', '.', $item['weight']) : null,
        //    isset($item['depth']) ? (float) str_replace(',', '.', $item['depth']) : null,
        //    isset($item['width']) ? (float) str_replace(',', '.', $item['width']) : null,
        //    isset($item['height']) ? (float) str_replace(',', '.', $item['height']) : null,
        //    isset($item['ncm']) ? $formatter->sanitizeNumber($item['ncm']) : null,
        //    (float) $formatter->price(str_replace(',', '.', $item['price'] ?? $item['price_compare'] ?? 0)),
        //    isset($item['price_compare']) ? (float) $formatter->price(str_replace(',', '.', $item['price_compare'])) : null,
        //    $item['warranty'] ?? null,
        //    $item['model'] ?? null,
        //    $formatter->gender($item['gender'] ?? null),
        //    $formatter->ageGroup($item['age_group'] ?? null),
        //    $variations
        //);
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
