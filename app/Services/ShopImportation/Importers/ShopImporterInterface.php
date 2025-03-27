<?php

namespace App\Services\ShopImportation\Importers;
use App\Services\ShopImportation\Events\ShopImportationEvent;

interface ShopImporterInterface
{
    function process(ShopImportationEvent $event): void;

    function import(ShopImportationEvent $event): void;
}