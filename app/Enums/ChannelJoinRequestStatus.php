<?php

namespace App\Enums;

enum ChannelJoinRequestStatus: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
}