<?php

declare(strict_types=1);

namespace Battle\Exception;

use Exception;

class DamageActionException extends Exception
{
    public const NO_DEFINED = 'No defined unit';
    public const NO_DEFINED_AGAIN = 'Не смотря на то, что команда сказала, что у неё есть живые юниты, метод getUnitForAttacks() не вернул ничего';
}
