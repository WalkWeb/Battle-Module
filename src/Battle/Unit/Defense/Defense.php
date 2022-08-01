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
    private $magicDefence;

    /**
     * @var int - Блок (0-100%)
     */
    private $block;

    /**
     * @var int - Магический блок (0-100%)
     */
    private $magicBlock;

    /**
     * @var int - Ментальный барьер (0-100%)
     */
    private $mentalBarrier;

    /**
     * @param int $defense
     * @param int $magicDefence
     * @param int $block
     * @param int $magicBlock
     * @param int $mentalBarrier
     * @throws DefenseException
     */
    public function __construct(int $defense, int $magicDefence, int $block, int $magicBlock, int $mentalBarrier)
    {
        $this->setDefense($defense);
        $this->setMagicDefense($magicDefence);
        $this->setBlock($block);
        $this->setMagicBlock($magicBlock);
        $this->setMentalBarrier($mentalBarrier);
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
    public function getMagicDefense(): int
    {
        return $this->magicDefence;
    }

    /**
     * @param int $magicDefense
     * @throws DefenseException
     */
    public function setMagicDefense(int $magicDefense): void
    {
        if ($magicDefense < self::MIN_MAGIC_DEFENSE || $magicDefense > self::MAX_MAGIC_DEFENSE) {
            throw new DefenseException(
                DefenseException::INCORRECT_MAGIC_DEFENSE_VALUE . DefenseInterface::MIN_MAGIC_DEFENSE . '-' . DefenseInterface::MAX_MAGIC_DEFENSE
            );
        }

        $this->magicDefence = $magicDefense;
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

    /**
     * @return int
     */
    public function getMagicBlock(): int
    {
        return $this->magicBlock;
    }

    /**
     * @param int $magicBlock
     * @throws DefenseException
     */
    public function setMagicBlock(int $magicBlock): void
    {
        if ($magicBlock < self::MIN_MAGIC_BLOCK || $magicBlock > self::MAX_MAGIC_BLOCK) {
            throw new DefenseException(
                DefenseException::INCORRECT_MAGIC_BLOCK_VALUE . DefenseInterface::MIN_MAGIC_BLOCK . '-' . DefenseInterface::MAX_MAGIC_BLOCK
            );
        }

        $this->magicBlock = $magicBlock;
    }

    /**
     * @return int
     */
    public function getMentalBarrier(): int
    {
        return $this->mentalBarrier;
    }

    /**
     * @param int $mentalBarrier
     * @throws DefenseException
     */
    public function setMentalBarrier(int $mentalBarrier): void
    {
        if ($mentalBarrier < self::MIN_MENTAL_BARRIER || $mentalBarrier > self::MAX_MENTAL_BARRIER) {
            throw new DefenseException(
                DefenseException::INCORRECT_MENTAL_BARRIER_VALUE . DefenseInterface::MIN_MENTAL_BARRIER . '-' . DefenseInterface::MAX_MENTAL_BARRIER
            );
        }

        $this->mentalBarrier = $mentalBarrier;
    }
}
