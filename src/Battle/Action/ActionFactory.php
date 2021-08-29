<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\BattleException;
use Battle\Traits\Validation;

class ActionFactory
{
    use Validation;

    private static $map = [
        ActionInterface::DAMAGE => DamageAction::class,
        ActionInterface::HEAL   => HealAction::class,
        ActionInterface::WAIT   => WaitAction::class,
        ActionInterface::SUMMON => SummonAction::class,
        ActionInterface::BUFF   => BuffAction::class,
        ActionInterface::EFFECT => EffectAction::class,
    ];

    /**
     * Создает Action на основе массива параметров
     *
     * TODO Пока реализовано только создание DamageAction и HealAction
     *
     * Пример данных:
     *
     * [
     *     'type'           => ActionInterface::DAMAGE,
     *     'action_unit'    => $actionUnit,
     *     'enemy_command'  => $enemyCommand,
     *     'allies_command' => $alliesCommand,
     *     'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
     * ]
     *
     * @param array $data
     * @return ActionInterface
     * @throws BattleException
     * @throws ActionException
     */
    public function create(array $data): ActionInterface
    {
        $type = self::existAndInt($data, 'type', ActionException::INVALID_TYPE_DATA);

        if (!array_key_exists($type, self::$map)) {
            throw new ActionException(ActionException::UNKNOWN_TYPE_ACTION);
        }

        $actionUnit = self::unit($data, 'action_unit', ActionException::INVALID_ACTION_UNIT_DATA);
        $enemyCommand = self::command($data, 'enemy_command', ActionException::INVALID_COMMAND_DATA);
        $alliesCommand = self::command($data, 'allies_command', ActionException::INVALID_COMMAND_DATA);
        $typeTarget = self::int($data, 'type_target', ActionException::INVALID_TYPE_TARGET_DATA);

        $className = self::$map[$type];

        if ($className === DamageAction::class || $className === HealAction::class) {

            $power = self::intOrNull($data, 'power', ActionException::INVALID_POWER_DATA);
            $name = self::stringOrNull($data, 'name', ActionException::INVALID_NAME_DATA);

            return new $className(
                $actionUnit,
                $enemyCommand,
                $alliesCommand,
                $typeTarget,
                $power,
                $name
            );
        }

        throw new ActionException(ActionException::NO_REALIZE);
    }
}
