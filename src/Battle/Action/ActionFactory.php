<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Traits\IdTrait;
use Battle\Traits\ValidationTrait;
use Battle\Unit\Effect\EffectCollection;
use Battle\Unit\Effect\EffectFactory;
use Battle\Unit\UnitFactory;
use Exception;

class ActionFactory
{
    use ValidationTrait;
    use IdTrait;

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
     * @throws Exception
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

        $className = self::$map[$type];

        if ($className === DamageAction::class || $className === HealAction::class) {

            $typeTarget = self::int($data, 'type_target', ActionException::INVALID_TYPE_TARGET_DATA);
            $power = self::intOrNull($data, 'power', ActionException::INVALID_POWER_DATA);
            $name = self::stringOrNull($data, 'name', ActionException::INVALID_NAME_DATA);
            $animationMethod = self::stringOrNull($data, 'animation_method', ActionException::INVALID_NAME_DATA);

            return new $className(
                $actionUnit,
                $enemyCommand,
                $alliesCommand,
                $typeTarget,
                $power,
                $name,
                $animationMethod
            );
        }

        if ($className === WaitAction::class) {
            return new WaitAction(
                $actionUnit,
                $enemyCommand,
                $alliesCommand
            );
        }

        if ($className === SummonAction::class) {

            $name = self::string($data, 'name', ActionException::INVALID_NAME_DATA);
            $summonData = self::array($data, 'summon', ActionException::INVALID_SUMMON_DATA);
            $summonData['id'] = self::generateId();
            $summonData['command'] = $actionUnit->getCommand();

            return new SummonAction(
                $actionUnit,
                $enemyCommand,
                $alliesCommand,
                $name,
                UnitFactory::create($summonData, $actionUnit->getContainer())
            );

        }

        if ($className === BuffAction::class) {

            $typeTarget = self::int($data, 'type_target', ActionException::INVALID_TYPE_TARGET_DATA);
            $name = self::string($data, 'name', ActionException::INVALID_NAME_DATA);
            $modifyMethod = self::string($data, 'modify_method', ActionException::INVALID_MODIFY_METHOD_DATA);
            $power = self::int($data, 'power', ActionException::INVALID_POWER_DATA);

            return new BuffAction(
                $actionUnit,
                $enemyCommand,
                $alliesCommand,
                $typeTarget,
                $name,
                $modifyMethod,
                $power
            );
        }

        $typeTarget = self::int($data, 'type_target', ActionException::INVALID_TYPE_TARGET_DATA);
        $name = self::string($data, 'name', ActionException::INVALID_NAME_DATA);
        $effectsData = self::array($data, 'effects', ActionException::INVALID_EFFECTS_DATA);
        $effects = new EffectCollection();
        $effectFactory = new EffectFactory($this);

        foreach ($effectsData as $effectData) {
            if (!is_array($effectData)) {
                throw new ActionException(ActionException::INVALID_EFFECT_DATA);
            }

            $effects->add($effectFactory->create($effectData));
        }

        return new EffectAction(
            $actionUnit,
            $enemyCommand,
            $alliesCommand,
            $typeTarget,
            $name,
            $effects
        );
    }
}