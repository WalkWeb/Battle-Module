<?php

declare(strict_types=1);

namespace Battle\Unit\Defense;

// TODO В сеттерах добавить проверки на допустимое значение

class Defense implements DefenseInterface
{
    /**
     * @var int
     */
    private $defense;

    /**
     * @var int
     */
    private $block;

    public function __construct(int $defense, int $block)
    {
        $this->defense = $defense;
        $this->block = $block;
    }

    /**
     * @return int
     */
    public function getDefense(): int
    {
        return $this->defense;
    }

    /**
     * @param int $defense
     */
    public function setDefense(int $defense): void
    {
        $this->defense = $defense;
    }

    /**
     * @return int
     */
    public function getBlock(): int
    {
        return $this->block;
    }

    /**
     * @param int $block
     */
    public function setBlock(int $block): void
    {
        $this->block = $block;
    }
}
