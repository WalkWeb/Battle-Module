<?php

declare(strict_types=1);

namespace Battle\Unit\Ability;

use Battle\Action\ActionCollection;
use Battle\Command\CommandInterface;
use Battle\Traits\AbilityDataTrait;
use Battle\Unit\UnitInterface;
use Exception;

class Ability extends AbstractAbility
{
    use AbilityDataTrait;

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

        foreach ($this->getActions($enemyCommand, $alliesCommand) as $action) {
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
    public function getActions(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        $actions = new ActionCollection();
        foreach ($this->actionsData as &$actionData) {
            $this->addParameters($actionData, $this->unit, $enemyCommand, $alliesCommand);
            $actions->add($this->actionFactory->create($actionData));
        }

        return $actions;
    }

    /**
     * @param UnitInterface $unit
     * @param bool $testMode
     * @throws Exception
     */
    public function update(UnitInterface $unit, bool $testMode = false): void
    {
        // Если способность одноразовая и уже была использована
        if ($this->disposable && $this->usage) {
            $this->ready = false;
            return;
        }

        // Если тип оружия указан, и он не подходит
        if (count($this->allowedWeaponTypes) !== 0 && !in_array($unit->getOffense()->getWeaponType()->getId(), $this->allowedWeaponTypes, true)) {
            $this->ready = false;
            return;
        }

        // Базовые проверки на активацию
        switch ($this->typeActivate) {
            case self::ACTIVATE_CONCENTRATION:
                $this->ready = $unit->getConcentration() === UnitInterface::MAX_CONCENTRATION;
                break;
            case self::ACTIVATE_RAGE:
                $this->ready = $unit->getRage() === UnitInterface::MAX_RAGE;
                break;
            case self::ACTIVATE_LOW_LIFE:
                $this->ready = !$this->usage && $this->unit->getLife() < $this->unit->getTotalLife() * 0.3;
                break;
            case self::ACTIVATE_DEAD:
                if ($testMode) {
                    $this->ready = !$this->unit->isAlive();
                } else {
                    $this->ready = !$this->unit->isAlive() && random_int(0, 100) <= $this->chanceActivate;
                }
                break;
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
