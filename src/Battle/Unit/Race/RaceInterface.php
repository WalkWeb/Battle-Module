<?php

declare(strict_types=1);

namespace Battle\Unit\Race;

use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\UnitInterface;

interface RaceInterface
{
    /**
     * Возвращает ID расы
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Возвращает имя расы
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Возвращает имя расы в единственном числе (например "Человек")
     *
     * @return string
     */
    public function getSingleName(): string;

    /**
     * Возвращает цвет расы, для цветового выделения имени юнита данной расы
     *
     * @return string
     */
    public function getColor(): string;

    /**
     * Возвращает url-путь к иконке расы
     *
     * @return string
     */
    public function getIcon(): string;

    /**
     * Возвращает коллекцию способностей данного класса
     *
     * @param UnitInterface $unit
     * @return AbilityCollection
     */
    public function getAbilities(UnitInterface $unit): AbilityCollection;
}
