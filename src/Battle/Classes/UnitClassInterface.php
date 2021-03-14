<?php

declare(strict_types=1);

namespace Battle\Classes;

use Battle\Action\ActionCollection;
use Battle\Command;
use Battle\Unit\UnitInterface;

interface UnitClassInterface
{
    public const WARRIOR = 1;
    public const PRIEST  = 2;

    public function getId(): int;
    public function getAbility(UnitInterface $actionUnit, Command $enemyCommand, Command $alliesCommand): ActionCollection;
}
