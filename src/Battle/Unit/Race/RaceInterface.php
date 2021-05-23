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
}
