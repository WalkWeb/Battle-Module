<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Container\ContainerInterface;
use Battle\Traits\IdTrait;
use Battle\Traits\ValidationTrait;
use Battle\Unit\Offense\MultipleOffense\MultipleOffenseFactory;
use Battle\Unit\Offense\OffenseFactory;
use Battle\Unit\UnitFactory;
use Exception;

class ActionFactory
{
    protected ContainerInterface $container;

    use ValidationTrait;
    use IdTrait;

    private array $methodMap = [
        ActionInterface::DAMAGE       => 'createDamageAction',
        ActionInterface::HEAL         => 'createHealAction',
        ActionInterface::WAIT         => 'createWaitAction',
        ActionInterface::SUMMON       => 'createSummonAction',
        ActionInterface::BUFF         => 'createBuffAction',
        ActionInterface::EFFECT       => 'createEffectAction',
        ActionInterface::RESURRECTION => 'createResurrectionAction',
        ActionInterface::PARALYSIS    => 'createParalysisAction',
        ActionInterface::MANA_RESTORE => 'createManaRestoreAction',
    ];

    public function __construct(ContainerInterface $container, array $methodMap = [])
    {
        $this->container = $container;

        if ($methodMap) {
            $this->methodMap = $methodMap;
        }
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
     * @uses createDamageAction, createHealAction, createWaitAction, createSummonAction, createBuffAction, createEffectAction, createResurrectionAction, createParalysisAction, createManaRestoreAction
     * @param array $data
     * @return ActionInterface
     * @throws Exception
     */
    public function create(array $data): ActionInterface
    {
        $type = self::int($data, 'type', ActionException::INVALID_TYPE_DATA);

        if (!array_key_exists($type, $this->methodMap)) {
            throw new ActionException(ActionException::UNKNOWN_TYPE_ACTION);
        }

        $factoryMethod = $this->methodMap[$type];

        if (!method_exists($this, $factoryMethod)) {
            throw new ActionException(ActionException::UNKNOWN_FACTORY_METHOD);
        }

        // Сразу проверяются общие для всех Action параметры
        self::unit($data, 'action_unit', ActionException::INVALID_ACTION_UNIT_DATA);
        self::command($data, 'enemy_command', ActionException::INVALID_COMMAND_DATA);
        self::command($data, 'allies_command', ActionException::INVALID_COMMAND_DATA);

        return $this->$factoryMethod($data);
    }

    /**
     * @param array $data
     * @return DamageAction
     * @throws Exception
     */
    private function createDamageAction(array $data): DamageAction
    {
        $offenseData = self::arrayOrNull($data, 'offense', ActionException::INVALID_OFFENSE_DATA);
        $multipleOffenseData = self::arrayOrNull($data, 'multiple_offense', ActionException::INVALID_MULTIPLE_OFFENSE_DATA);

        return new DamageAction(
            $this->container,
            $data['action_unit'],
            $data['enemy_command'],
            $data['allies_command'],
            self::int($data, 'type_target', ActionException::INVALID_TYPE_TARGET_DATA),
            self::bool($data, 'can_be_avoided', ActionException::INVALID_CAN_BE_AVOIDED),
            self::string($data, 'name', ActionException::INVALID_NAME_DATA),
            self::string($data, 'animation_method', ActionException::INVALID_ANIMATION_METHOD_DATA),
            self::string($data, 'message_method', ActionException::INVALID_MESSAGE_METHOD_DATA),
            $offenseData ? OffenseFactory::create($offenseData, $this->container) : null,
            $multipleOffenseData ? MultipleOffenseFactory::create($multipleOffenseData) : null,
            self::stringOrMissing($data, 'icon', ActionException::INVALID_ICON_DATA)
        );
    }

    /**
     * @param array $data
     * @return HealAction
     * @throws Exception
     */
    private function createHealAction(array $data): HealAction
    {
        return new HealAction(
            $this->container,
            $data['action_unit'],
            $data['enemy_command'],
            $data['allies_command'],
            self::int($data, 'type_target', ActionException::INVALID_TYPE_TARGET_DATA),
            self::int($data, 'power', ActionException::INVALID_POWER_DATA),
            self::stringOrNull($data, 'name', ActionException::INVALID_NAME_DATA),
            self::stringOrNull($data, 'animation_method', ActionException::INVALID_ANIMATION_METHOD_DATA),
            self::stringOrNull($data, 'message_method', ActionException::INVALID_MESSAGE_METHOD_DATA),
            self::stringOrMissing($data, 'icon', ActionException::INVALID_ICON_DATA)
        );
    }

    /**
     * @param array $data
     * @return WaitAction
     * @throws Exception
     */
    private function createWaitAction(array $data): WaitAction
    {
        return new WaitAction(
            $this->container,
            $data['action_unit'],
            $data['enemy_command'],
            $data['allies_command'],
        );
    }

    /**
     * @param array $data
     * @return SummonAction
     * @throws Exception
     */
    private function createSummonAction(array $data): SummonAction
    {
        $actionUnit = self::unit($data, 'action_unit', ActionException::INVALID_ACTION_UNIT_DATA);
        $summonData = self::array($data, 'summon', ActionException::INVALID_SUMMON_DATA);
        $summonData['id'] = self::generateId();
        $summonData['command'] = $actionUnit->getCommand();

        return new SummonAction(
            $this->container,
            $actionUnit,
            $data['enemy_command'],
            $data['allies_command'],
            self::string($data, 'name', ActionException::INVALID_NAME_DATA),
            UnitFactory::create($summonData, $actionUnit->getContainer()),
            self::stringOrMissing($data, 'icon', ActionException::INVALID_ICON_DATA)
        );
    }

    /**
     * @param array $data
     * @return BuffAction
     * @throws Exception
     */
    private function createBuffAction(array $data): BuffAction
    {
        return new BuffAction(
            $this->container,
            $data['action_unit'],
            $data['enemy_command'],
            $data['allies_command'],
            self::int($data, 'type_target', ActionException::INVALID_TYPE_TARGET_DATA),
            self::string($data, 'name', ActionException::INVALID_NAME_DATA),
            self::string($data, 'modify_method', ActionException::INVALID_MODIFY_METHOD_DATA),
            self::int($data, 'power', ActionException::INVALID_POWER_DATA),
            self::stringOrNull($data, 'message_method', ActionException::INVALID_MESSAGE_METHOD_DATA)
        );
    }

    /**
     * @param array $data
     * @return EffectAction
     * @throws Exception
     */
    private function createEffectAction(array $data): EffectAction
    {
        return new EffectAction(
            $this->container,
            $data['action_unit'],
            $data['enemy_command'],
            $data['allies_command'],
            self::int($data, 'type_target', ActionException::INVALID_TYPE_TARGET_DATA),
            self::string($data, 'name', ActionException::INVALID_NAME_DATA),
            self::stringOrMissing($data, 'icon', ActionException::INVALID_ICON_DATA),
            $this->container->getEffectFactory()->create(self::array($data, 'effect', ActionException::INVALID_EFFECT_DATA)),
            self::stringOrNull($data, 'animation_method', ActionException::INVALID_ANIMATION_METHOD_DATA),
            self::stringOrNull($data, 'message_method', ActionException::INVALID_MESSAGE_METHOD_DATA)
        );
    }

    /**
     * @param array $data
     * @return ResurrectionAction
     * @throws Exception
     */
    private function createResurrectionAction(array $data): ResurrectionAction
    {
        return new ResurrectionAction(
            $this->container,
            $data['action_unit'],
            $data['enemy_command'],
            $data['allies_command'],
            self::int($data, 'type_target', ActionException::INVALID_TYPE_TARGET_DATA),
            self::int($data, 'power', ActionException::INVALID_POWER_DATA),
            self::string($data, 'name', ActionException::INVALID_NAME_DATA),
            self::string($data, 'message_method', ActionException::INVALID_MESSAGE_METHOD_DATA),
            self::stringOrMissing($data, 'icon', ActionException::INVALID_ICON_DATA)
        );
    }

    /**
     * @param array $data
     * @return ParalysisAction
     * @throws Exception
     */
    private function createParalysisAction(array $data): ParalysisAction
    {
        return new ParalysisAction(
            $this->container,
            $data['action_unit'],
            $data['enemy_command'],
            $data['allies_command'],
            self::stringOrNull($data, 'message_method', ActionException::INVALID_MESSAGE_METHOD_DATA)
        );
    }

    /**
     * @param array $data
     * @return ManaRestoreAction
     * @throws Exception
     */
    private function createManaRestoreAction(array $data): ManaRestoreAction
    {
        return new ManaRestoreAction(
            $this->container,
            $data['action_unit'],
            $data['enemy_command'],
            $data['allies_command'],
            self::int($data, 'type_target', ActionException::INVALID_TYPE_TARGET_DATA),
            self::int($data, 'power', ActionException::INVALID_POWER_DATA),
            self::string($data, 'name', ActionException::INVALID_NAME_DATA),
            self::string($data, 'animation_method', ActionException::INVALID_ANIMATION_METHOD_DATA),
            self::string($data, 'message_method', ActionException::INVALID_MESSAGE_METHOD_DATA),
            self::stringOrMissing($data, 'icon', ActionException::INVALID_ICON_DATA)
        );
    }
}
