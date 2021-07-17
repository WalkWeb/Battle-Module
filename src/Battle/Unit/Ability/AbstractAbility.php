<?php

declare(strict_types=1);

namespace Battle\Unit\Ability;

use Battle\Container\ContainerInterface;
use Battle\Unit\UnitInterface;

abstract class AbstractAbility implements AbilityInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $icon;

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
     * @param string $name
     * @param string $icon
     * @param UnitInterface $unit
     */
    public function __construct(string $name, string $icon, UnitInterface $unit)
    {
        $this->name = $name;
        $this->icon = $icon;
        $this->unit = $unit;
        $this->container = $unit->getContainer();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function isReady(): bool
    {
        return $this->ready;
    }

    public function getUnit(): UnitInterface
    {
        return $this->unit;
    }
}
