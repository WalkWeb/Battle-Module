<?php

declare(strict_types=1);

namespace Battle\Traits;

use Battle\Command\CommandInterface;
use Battle\Unit\Ability\AbilityException;
use Battle\Unit\UnitInterface;

/**
 * В этом трейте содержатся методы для добавления в данные по способности необходимых параметров, которые необходимы
 * для создания Action
 *
 * @package Battle\Traits
 */
trait AbilityDataTrait
{

    /**
     * Параметры способности изначально не связаны с юнитом (точнее есть только юнит, в момент создания способности, но
     * не на момент написания массива параметров способности) и командами - потому что изначально это просто массив с
     * параметрами.
     *
     * Но для создания Actions уже нужна информация по юниту, который делает ход, и о командах. По этому нужные
     * параметры добавляются в момент запроса Actions, когда юнит и команды уже существуют
     *
     * @param array $data
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @throws AbilityException
     */
    private function addParameters(array &$data, UnitInterface $unit, CommandInterface $enemyCommand, CommandInterface $alliesCommand): void
    {
        $data['action_unit'] = $unit;
        $data['enemy_command'] = $enemyCommand;
        $data['allies_command'] = $alliesCommand;

        if (array_key_exists('effect', $data)) {
            $this->validateEffectData($data['effect']);
            foreach ($data['effect']['on_apply_actions'] as &$onApplyActionData) {
                $this->addStageParameters($onApplyActionData, $unit, $enemyCommand, $alliesCommand);
            }
            unset($onApplyActionData);
            foreach ($data['effect']['on_next_round_actions'] as &$onNextRoundActionData) {
                $this->addStageParameters($onNextRoundActionData, $unit, $enemyCommand, $alliesCommand);
            }
            unset($onNextRoundActionData);
            foreach ($data['effect']['on_disable_actions'] as &$onDisableActionData) {
                $this->addStageParameters($onDisableActionData, $unit, $enemyCommand, $alliesCommand);
            }
            unset($onDisableActionData);
        }
    }

    /**
     * @param array $data
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     */
    private function addStageParameters(array &$data, UnitInterface $unit, CommandInterface $enemyCommand, CommandInterface $alliesCommand): void
    {
        $data['action_unit'] = $unit;
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
