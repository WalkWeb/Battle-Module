<?php

declare(strict_types=1);

namespace Battle\Unit\Offense;

class Offense implements OffenseInterface
{
    /**
     * @var int
     */
    private $damage;

    /**
     * @var float
     */
    private $attackSpeed;

    /**
     * @var int
     */
    private $accuracy;

    /**
     * @var int
     */
    private $blockIgnore;

    public function __construct(int $damage, float $attackSpeed, int $accuracy, int $blockIgnore)
    {
        $this->damage = $damage;
        $this->attackSpeed = $attackSpeed;
        $this->accuracy = $accuracy;
        $this->blockIgnore = $blockIgnore;
    }

    /**
     * @return int
     */
    public function getDamage(): int
    {
        return $this->damage;
    }

    /**
     * @return float
     */
    public function getAttackSpeed(): float
    {
        return $this->attackSpeed;
    }

    /**
     * @return int
     */
    public function getAccuracy(): int
    {
        return $this->accuracy;
    }

    /**
     * @return int
     */
    public function getBlockIgnore(): int
    {
        return $this->blockIgnore;
    }
}
