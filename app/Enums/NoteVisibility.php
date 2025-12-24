<?php

namespace App\Enums;

enum NoteVisibility: string
{
    case PRIVATE = 'PRIVATE';
    case PUBLIC = 'PUBLIC';
    case CHANNEL = 'CHANNEL';
}
