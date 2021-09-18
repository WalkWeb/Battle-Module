<?php

declare(strict_types=1);

namespace Battle\Unit\Classes;

use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\UnitInterface;

interface UnitClassInterface
{
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
