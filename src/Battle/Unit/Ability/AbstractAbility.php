<?php

declare(strict_types=1);

namespace Battle\Unit\Ability;

use Battle\Command\CommandInterface;
use Battle\Container\ContainerInterface;
use Battle\Unit\UnitInterface;

// TODO name должен задаваться через дочерние конкретные реализации, от указания через конструктор надо уйти
// TODO icon должен задаваться через дочерние конкретные реализации, от указания через конструктор надо уйти

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

    /**
     * Призыв новых существ всегда доступен - ограничений мест в команде нет
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return bool
     */
    public function canByUsed(CommandInterface $enemyCommand, CommandInterface $alliesCommand): bool
    {
        return true;
    }
}
