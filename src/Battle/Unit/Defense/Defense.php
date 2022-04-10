<?php

declare(strict_types=1);

namespace Battle\Unit\Defense;

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
     * @return int
     */
    public function getBlock(): int
    {
        return $this->block;
    }
}
