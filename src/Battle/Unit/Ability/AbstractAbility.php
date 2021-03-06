<?php

declare(strict_types=1);

namespace Battle\Unit\Ability;

use Battle\Action\ActionCollection;
use Battle\Command\CommandInterface;
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
     * TODO ContainerInterface на удаление - его можно получить из юнита
     *
     * @param string $name
     * @param string $icon
     * @param UnitInterface $unit
     * @param ContainerInterface $container
     */
    public function __construct(string $name, string $icon, UnitInterface $unit, ContainerInterface $container)
    {
        $this->name = $name;
        $this->icon = $icon;
        $this->unit = $unit;
        $this->container = $container;
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

    public function setApply(): void
    {
        $this->ready = false;
    }

    abstract public function getAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection;

    abstract public function update(UnitInterface $unit): void;
}
