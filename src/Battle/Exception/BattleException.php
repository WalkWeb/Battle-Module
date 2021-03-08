<?php

declare(strict_types=1);

namespace Battle\Exception;

use Exception;

class BattleException extends Exception
{
    public const UNEXPECTED_ENDING_BATTLE = 'Превышен лимит раундов';
}
