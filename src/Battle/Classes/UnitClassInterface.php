<?php

declare(strict_types=1);

namespace Battle\Classes;

use Battle\Action\ActionCollection;
use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;

interface UnitClassInterface
{
    public const WARRIOR = 1;
    public const PRIEST  = 2;

    public const WARRIOR_SMALL_ICON = '/images/icons/small/warrior.png';
    public const PRIEST_SMALL_ICON  = '/images/icons/small/priest.png';

    /**
     * Возвращает ID класса
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Возвращает способность данного класса для использования её в бою
     *
     * Так как способность может состоять сразу из нескольких действий - возвращается ActionCollection
     *
     * @param UnitInterface $actionUnit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     */
    public function getAbility(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand
    ): ActionCollection;

    /**
     * Url к мини-иконке класса в размере 21x21, для отображения в бою
     *
     * @return string
     */
    public function getSmallIcon(): string;
}
