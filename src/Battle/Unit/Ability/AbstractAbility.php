<?php

declare(strict_types=1);

namespace Battle\Unit\Ability;

use Battle\Command\CommandInterface;
use Battle\Container\ContainerInterface;
use Battle\Unit\UnitInterface;

abstract class AbstractAbility implements AbilityInterface
{
    /**
     * @var bool
     */
    protected $ready = false;

    /**
     * @var UnitInterface
     */
    protected $unit;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param UnitInterface $unit
     */
    public function __construct(UnitInterface $unit)
    {
        $this->unit = $unit;
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
}
