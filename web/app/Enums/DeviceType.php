<?php

namespace App\Enums;

enum DeviceType: string
{
    case Ios = 'ios';
    case Android = 'android';
    case Web = 'web';
}
