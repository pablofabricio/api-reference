<?php

namespace App\Enums;

enum ReferenceNodeType: string
{
    case BOOK = 'BOOK';
    case CHAPTER = 'CHAPTER';
    case VERSE = 'VERSE';
    case PAGE = 'PAGE';
    case SESSION = 'SESSION';
    case STANZA = 'STANZA';
    case LINE = 'LINE';
    case PARAGRAPH = 'PARAGRAPH';
}
