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
     * @param int $defense
     * @throws DefenseException
     */
    public function setDefense(int $defense): void
    {
        if ($defense < self::MIN_DEFENSE || $defense > self::MAX_DEFENSE) {
            throw new DefenseException(
                DefenseException::INCORRECT_DEFENSE_VALUE . DefenseInterface::MIN_DEFENSE . '-' . DefenseInterface::MAX_DEFENSE
            );
        }

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
     * @throws DefenseException
     */
    public function setBlock(int $block): void
    {
        if ($block < self::MIN_BLOCK || $block > self::MAX_BLOCK) {
            throw new DefenseException(
                DefenseException::INCORRECT_BLOCK_VALUE . DefenseInterface::MIN_BLOCK . '-' . DefenseInterface::MAX_BLOCK
            );
        }

        $this->block = $block;
    }
}
