<?php

namespace App\Enums;

enum ChannelMemberRole: string
{
    case OWNER = 'OWNER';
    case MODERATOR = 'MODERATOR';
    case MEMBER = 'MEMBER';
}
