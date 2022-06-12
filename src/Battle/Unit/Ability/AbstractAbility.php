<?php

declare(strict_types=1);

namespace Battle\Unit\Ability;

use Battle\Container\ContainerInterface;
use Battle\Unit\UnitInterface;

abstract class AbstractAbility implements AbilityInterface
{
    /**
     * @var bool
     */
    protected $ready = false;

    /**
     * @var bool
     */
    protected $disposable;

    /**
     * @var bool
     */
    protected $usage = false;

    /**
     * @var UnitInterface
     */
    protected $unit;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var int
     */
    protected $chanceActivate;

    /**
     * @param UnitInterface $unit
     * @param bool $disposable
     * @param int $chanceActivate
     */
    public function __construct(UnitInterface $unit, bool $disposable, int $chanceActivate = 100)
    {
        $this->unit = $unit;
        $this->disposable = $disposable;
        $this->chanceActivate = $chanceActivate;
        $this->container = $unit->getContainer();
    }

    /**
     * @return bool
     */
    public function isReady(): bool
    {
        return $this->ready;
    }

    /**
     * @return UnitInterface
     */
    public function getUnit(): UnitInterface
    {
        return $this->unit;
    }

    /**
     * @return bool
     */
    public function isDisposable(): bool
    {
        return $this->disposable;
    }

    /**
     * @return bool
     */
    public function isUsage(): bool
    {
        return $this->usage;
    }

    /**
     * @return int
     */
    public function getChanceActivate(): int
    {
        return $this->chanceActivate;
    }
}
