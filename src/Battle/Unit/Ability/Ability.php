<?php

declare(strict_types=1);

namespace Battle\Unit\Ability;

use Battle\Action\ActionCollection;
use Battle\Action\ActionFactory;
use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;
use Exception;

/**
 * TODO В будущем не будет отдельных классов на каждую способность, а будет один универсальный класс способности,
 * TODO который будет создаваться на основе массива с параметрами
 *
 * TODO Но т.к. это трудоемкий переход - он будет делаться постепенно, и на это время будут существовать сразу оба
 * TODO варианта с отдельными классами и одним универсальным
 *
 * @package Battle\Unit\Ability
 */
class Ability extends AbstractAbility
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $icon;

    /**
     * @var array
     */
    private $actionsData;

    /**
     * @var int
     */
    private $typeActivate;

    /**
     * @var int
     */
    private $chanceActivate;

    public function __construct(
        UnitInterface $unit,
        bool $disposable,
        string $name,
        string $icon,
        array $actionsData,
        int $typeActivate,
        int $chanceActivate = 100
    )
    {
        parent::__construct($unit, $disposable);
        $this->name = $name;
        $this->icon = $icon;
        $this->actionsData = $actionsData;
        $this->typeActivate = $typeActivate;
        $this->chanceActivate = $chanceActivate;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return bool
     * @throws Exception
     */
    public function canByUsed(CommandInterface $enemyCommand, CommandInterface $alliesCommand): bool
    {
        if ($this->disposable && $this->usage) {
            return false;
        }

        foreach ($this->getAction($enemyCommand, $alliesCommand) as $action) {
            if (!$action->canByUsed()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws Exception
     */
    public function getAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        $actions = new ActionCollection();
        foreach ($this->actionsData as $actionData) {
            // TODO Проверка, что $actionData это массив
            $actions->add((new ActionFactory())->create($actionData));
        }

        return $actions;
    }

    public function update(UnitInterface $unit, bool $testMode = false): void
    {
        if ($this->disposable && $this->usage) {
            $this->ready = false;
            return;
        }

        // TODO Переписать на switch
        if ($this->typeActivate === self::ACTIVATE_CONCENTRATION) {
            $this->ready = $unit->getConcentration() === UnitInterface::MAX_CONCENTRATION;
        }
        if ($this->typeActivate === self::ACTIVATE_RAGE) {
            $this->ready = $unit->getRage() === UnitInterface::MAX_RAGE;
        }
        if ($this->typeActivate === self::ACTIVATE_LOW_LIFE) {
            $this->ready = !$this->usage && $this->unit->getLife() < $this->unit->getTotalLife() * 0.3;
        }
        if ($this->typeActivate === self::ACTIVATE_DEAD) {
            if ($testMode) {
                $this->ready = !$this->unit->isAlive();
            } else {
                $this->ready = !$this->unit->isAlive() && random_int(0, 100) <= $this->chanceActivate;
            }
        }
    }

    public function usage(): void
    {
        $this->ready = false;
        $this->usage = true;

        if ($this->typeActivate === self::ACTIVATE_CONCENTRATION) {
            $this->unit->useConcentrationAbility();
        }
        if ($this->typeActivate === self::ACTIVATE_RAGE) {
            $this->unit->useRageAbility();
        }
    }
}
