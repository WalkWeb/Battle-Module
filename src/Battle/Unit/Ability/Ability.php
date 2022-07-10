<?php

declare(strict_types=1);

namespace Battle\Unit\Ability;

use Battle\Action\ActionCollection;
use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;
use Exception;

class Ability extends AbstractAbility
{
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
            $this->addParameters($actionData, $enemyCommand, $alliesCommand);
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
        if ($this->disposable && $this->usage) {
            $this->ready = false;
            return;
        }

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

    /**
     * Параметры способности изначально не связаны с юнитом (точнее есть только юнит, в момент создания способности, но
     * не на момент написания массива параметров способности) и командами - потому что изначально это просто массив с
     * параметрами.
     *
     * Но для создания Actions уже нужна информация по юниту, который делает ход, и о командах. По этому нужные
     * параметры добавляются в момент запроса Actions, когда юнит и команды уже существуют
     *
     * @param array $data
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @throws AbilityException
     */
    private function addParameters(array &$data, CommandInterface $enemyCommand, CommandInterface $alliesCommand): void
    {
        $data['action_unit'] = $this->unit;
        $data['enemy_command'] = $enemyCommand;
        $data['allies_command'] = $alliesCommand;

        if (array_key_exists('effect', $data)) {
            $this->validateEffectData($data['effect']);
            foreach ($data['effect']['on_apply_actions'] as &$onApplyActionData) {
                $this->addStageParameters($onApplyActionData, $enemyCommand, $alliesCommand);
            }
            unset($onApplyActionData);
            foreach ($data['effect']['on_next_round_actions'] as &$onNextRoundActionData) {
                $this->addStageParameters($onNextRoundActionData, $enemyCommand, $alliesCommand);
            }
            unset($onNextRoundActionData);
            foreach ($data['effect']['on_disable_actions'] as &$onDisableActionData) {
                $this->addStageParameters($onDisableActionData, $enemyCommand, $alliesCommand);
            }
            unset($onDisableActionData);
        }
    }

    /**
     * @param array $data
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     */
    private function addStageParameters(array &$data, CommandInterface $enemyCommand, CommandInterface $alliesCommand): void
    {
        $data['action_unit'] = $this->unit;
        $data['enemy_command'] = $enemyCommand;
        $data['allies_command'] = $alliesCommand;
    }

    /**
     * Проверяет наличие необходимых параметров в массиве параметров для создания эффекта:
     * "on_apply_actions"
     * "on_next_round_actions"
     * "on_disable_actions"
     *
     * @param array $data
     * @throws AbilityException
     */
    private function validateEffectData(array $data): void
    {
        if (
            !array_key_exists('on_apply_actions', $data) ||
            !array_key_exists('on_next_round_actions', $data) ||
            !array_key_exists('on_disable_actions', $data)
        ) {
            throw new AbilityException(AbilityException::INVALID_EFFECT_DATA);
        }
    }
}
