<?php

declare(strict_types=1);

namespace Battle\Unit\Classes;

use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\UnitInterface;

interface UnitClassInterface
{
    // ===== ID =====
    public const WARRIOR_ID     = 1;
    public const PRIEST_ID      = 2;
    public const DEAD_KNIGHT_ID = 3;
    public const DARK_MAGE_ID   = 4;
    public const TITAN_ID       = 5;

    // ===== Names =====
    public const WARRIOR_NAME     = 'Warrior';
    public const PRIEST_NAME      = 'Priest';
    public const DEAD_KNIGHT_NAME = 'Dead Knight';
    public const DARK_MAGE_NAME   = 'Dark Mage';
    public const TITAN_NAME       = 'Titan';

    // ===== Icons =====
    public const WARRIOR_SMALL_ICON     = '/images/icons/small/warrior.png';
    public const PRIEST_SMALL_ICON      = '/images/icons/small/priest.png';
    public const DEAD_KNIGHT_SMALL_ICON = '/images/icons/small/dead-knight.png';
    public const DARK_MAGE_SMALL_ICON   = '/images/icons/small/dark-mage.png';
    public const TITAN_SMALL_ICON       = '/images/icons/small/titan.png';

    /**
     * Возвращает ID класса
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Возвращает имя класса
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Url к мини-иконке класса в размере 21x21, для отображения в бою
     *
     * @return string
     */
    public function getSmallIcon(): string;

    /**
     * Возвращает коллекцию способностей данного класса
     *
     * @param UnitInterface $unit
     * @return AbilityCollection
     */
    public function getAbilities(UnitInterface $unit): AbilityCollection;
}
