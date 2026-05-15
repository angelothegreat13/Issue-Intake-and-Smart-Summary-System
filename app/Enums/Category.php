<?php

namespace App\Enums;

enum Category: string
{
    case Bug            = 'bug';
    case Feature        = 'feature';
    case Infrastructure = 'infrastructure';
    case Performance    = 'performance';
    case Data           = 'data';
    case Security       = 'security';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
