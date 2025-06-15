<?php

declare(strict_types=1);

namespace Battle\Weapon\Type;

use Exception;

class WeaponTypeException extends Exception
{
    public const string UNKNOWN_WEAPON_TYPE_ID = 'Unknown weapon type id';
}
