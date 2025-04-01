<?php

namespace App\Services\ShopImportation\Enums;

abstract class EventsStatusEnum
{
    const ON_QUEUE = 'on_queue';
    const ON_IMPORT = 'on_import';
    const FINISHED = 'finished';
}