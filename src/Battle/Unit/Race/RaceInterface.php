<?php

declare(strict_types=1);

namespace Battle\Unit\Race;

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
     * Возвращает массив (названий классов) способностей - базовых, для этой расы
     *
     * AbilityCollection не используется, потому что для создания самой способности нужен объект юнита, а его на момент
     * создания объекта расы еще нет
     *
     * @return string[]
     */
    public function getAbilities(): array;
}
