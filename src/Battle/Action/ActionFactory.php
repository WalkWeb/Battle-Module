<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Container\ContainerInterface;
use Battle\Traits\IdTrait;
use Battle\Traits\ValidationTrait;
use Battle\Unit\Effect\EffectFactory;
use Battle\Unit\Offense\OffenseFactory;
use Battle\Unit\UnitFactory;
use Exception;

class ActionFactory
{
    protected ContainerInterface $container;

    use ValidationTrait;
    use IdTrait;

    private static array $map = [
        ActionInterface::DAMAGE       => DamageAction::class,
        ActionInterface::HEAL         => HealAction::class,
        ActionInterface::WAIT         => WaitAction::class,
        ActionInterface::SUMMON       => SummonAction::class,
        ActionInterface::BUFF         => BuffAction::class,
        ActionInterface::EFFECT       => EffectAction::class,
        ActionInterface::RESURRECTION => ResurrectionAction::class,
        ActionInterface::PARALYSIS    => ParalysisAction::class,
    ];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

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
        $type = self::int($data, 'type', ActionException::INVALID_TYPE_DATA);

        if (!array_key_exists($type, self::$map)) {
            throw new ActionException(ActionException::UNKNOWN_TYPE_ACTION);
        }

        $actionUnit = self::unit($data, 'action_unit', ActionException::INVALID_ACTION_UNIT_DATA);
        $enemyCommand = self::command($data, 'enemy_command', ActionException::INVALID_COMMAND_DATA);
        $alliesCommand = self::command($data, 'allies_command', ActionException::INVALID_COMMAND_DATA);
        $icon = self::stringOrMissing($data, 'icon', ActionException::INVALID_ICON);

        $className = self::$map[$type];

        if ($className === DamageAction::class) {

            // TODO Добавить возможность offense быть null, и в этом случае будет браться Offense атакующего юнита
            $offenseData = self::array($data, 'offense', ActionException::INVALID_OFFENSE_DATA);
            $typeTarget = self::int($data, 'type_target', ActionException::INVALID_TYPE_TARGET_DATA);
            $canBeAvoided = self::bool($data, 'can_be_avoided', ActionException::INVALID_CAN_BE_AVOIDED);
            $name = self::string($data, 'name', ActionException::INVALID_NAME_DATA);
            $animationMethod = self::string($data, 'animation_method', ActionException::INVALID_ANIMATION_DATA);
            $messageMethod = self::string($data, 'message_method', ActionException::INVALID_MESSAGE_METHOD);

            return new DamageAction(
                $this->container,
                $actionUnit,
                $enemyCommand,
                $alliesCommand,
                $typeTarget,
                OffenseFactory::create($offenseData),
                $canBeAvoided,
                $name,
                $animationMethod,
                $messageMethod,
                $icon
            );
        }

        if ($className === HealAction::class) {

            $typeTarget = self::int($data, 'type_target', ActionException::INVALID_TYPE_TARGET_DATA);
            $power = self::int($data, 'power', ActionException::INVALID_POWER_DATA);
            $name = self::stringOrNull($data, 'name', ActionException::INVALID_NAME_DATA);
            $animationMethod = self::stringOrNull($data, 'animation_method', ActionException::INVALID_ANIMATION_DATA);
            $messageMethod = self::stringOrNull($data, 'message_method', ActionException::INVALID_MESSAGE_METHOD);

            return new HealAction(
                $this->container,
                $actionUnit,
                $enemyCommand,
                $alliesCommand,
                $typeTarget,
                $power,
                $name,
                $animationMethod,
                $messageMethod,
                $icon
            );
        }

        if ($className === WaitAction::class) {
            return new WaitAction(
                $this->container,
                $actionUnit,
                $enemyCommand,
                $alliesCommand
            );
        }

        if ($className === ParalysisAction::class) {
            return new ParalysisAction(
                $this->container,
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
                $this->container,
                $actionUnit,
                $enemyCommand,
                $alliesCommand,
                $name,
                UnitFactory::create($summonData, $actionUnit->getContainer()),
                $icon
            );

        }

        if ($className === BuffAction::class) {

            $typeTarget = self::int($data, 'type_target', ActionException::INVALID_TYPE_TARGET_DATA);
            $name = self::string($data, 'name', ActionException::INVALID_NAME_DATA);
            $modifyMethod = self::string($data, 'modify_method', ActionException::INVALID_MODIFY_METHOD_DATA);
            $power = self::int($data, 'power', ActionException::INVALID_POWER_DATA);
            $messageMethod = self::stringOrNull($data, 'message_method', ActionException::INVALID_MESSAGE_METHOD);

            return new BuffAction(
                $this->container,
                $actionUnit,
                $enemyCommand,
                $alliesCommand,
                $typeTarget,
                $name,
                $modifyMethod,
                $power,
                $messageMethod
            );
        }

        if ($className === ResurrectionAction::class) {
            $typeTarget = self::int($data, 'type_target', ActionException::INVALID_TYPE_TARGET_DATA);
            $power = self::int($data, 'power', ActionException::INVALID_POWER_DATA);
            $name = self::stringOrNull($data, 'name', ActionException::INVALID_NAME_DATA);
            $messageMethod = self::stringOrNull($data, 'message_method', ActionException::INVALID_MESSAGE_METHOD);

            return new ResurrectionAction(
                $this->container,
                $actionUnit,
                $enemyCommand,
                $alliesCommand,
                $typeTarget,
                $power,
                $name,
                $icon,
                $messageMethod
            );
        }

        $typeTarget = self::int($data, 'type_target', ActionException::INVALID_TYPE_TARGET_DATA);
        $name = self::string($data, 'name', ActionException::INVALID_NAME_DATA);
        $effectData = self::array($data, 'effect', ActionException::INVALID_EFFECT_DATA);
        $animationMethod = self::stringOrNull($data, 'animation_method', ActionException::INVALID_ANIMATION_DATA);
        $messageMethod = self::stringOrNull($data, 'message_method', ActionException::INVALID_MESSAGE_METHOD);

        $effectFactory = new EffectFactory($this);
        $effect = $effectFactory->create($effectData);

        return new EffectAction(
            $this->container,
            $actionUnit,
            $enemyCommand,
            $alliesCommand,
            $typeTarget,
            $name,
            $icon,
            $effect,
            $animationMethod,
            $messageMethod
        );
    }
}
