<?php

declare(strict_types=1);

namespace Battle\Weapon\Type;

use Battle\Action\ActionCollection;
use Battle\Command\CommandInterface;

interface WeaponTypeInterface
{
    public const NONE                 = 0; // Отсутствие типа оружия, например в уроне от эффекта
    public const SWORD                = 1;
    public const AXE                  = 2;
    public const MACE                 = 3;
    public const DAGGER               = 4;
    public const SPEAR                = 5;
    public const BOW                  = 6;
    public const STAFF                = 7;
    public const WAND                 = 8;
    public const TWO_HAND_SWORD       = 9;
    public const TWO_HAND_AXE         = 10;
    public const TWO_HAND_MACE        = 11;
    public const HEAVY_TWO_HAND_SWORD = 12;
    public const HEAVY_TWO_HAND_AXE   = 13;
    public const HEAVY_TWO_HAND_MACE  = 14;
    public const LANCE                = 15;
    public const CROSSBOW             = 16;
    public const UNARMED              = 17; // При сражении простыми кулаками

    /**
     * Возвращает ID типа оружия
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Возвращает название типа оружия
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Возвращает коллекцию Actions, которые будут применены к цели в случае критического удара. Например, булавы при
     * критическом ударе оглушают, а кинжалы вызывают кровотечение
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     */
    public function getActions(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection;
}
