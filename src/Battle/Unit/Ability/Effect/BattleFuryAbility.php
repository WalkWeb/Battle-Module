<?php

declare(strict_types=1);

namespace Battle\Unit\Ability\Effect;

use Battle\Action\ActionCollection;
use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\AbstractAbility;
use Battle\Unit\UnitInterface;
use Exception;

class BattleFuryAbility extends AbstractAbility
{
    private const NAME          = 'Battle Fury';
    private const ICON          = '/images/icons/ability/102.png';
    private const USE_MESSAGE   = 'use Battle Fury';
    private const DURATION      = 5;
    private const MODIFY_METHOD = 'multiplierAttackSpeed';
    private const MODIFY_POWER  = 140;

    /**
     * @var ActionCollection
     */
    private $actions;

    /**
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws Exception
     */
    public function getAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        return $this->createEffectActions($enemyCommand, $alliesCommand, true);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return self::ICON;
    }

    /**
     * Способность активируется при полной ярости юнита
     *
     * @param UnitInterface $unit
     */
    public function update(UnitInterface $unit): void
    {
        if (!$this->ready && $unit->getRage() === UnitInterface::MAX_RAGE) {
            $this->ready = true;
        }
    }

    /**
     * Способность отмечает свое использование - переходит в неактивный статус и обнуляет ярость у юнита
     */
    public function usage(): void
    {
        $this->ready = false;
        $this->unit->useRageAbility();
    }

    /**
     * Может ли способность быть применена - если аналогичный эффект существует, то нет
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return bool
     * @throws Exception
     */
    public function canByUsed(CommandInterface $enemyCommand, CommandInterface $alliesCommand): bool
    {
        foreach ($this->createEffectActions($enemyCommand, $alliesCommand) as $action) {
            if (!$action->canByUsed()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Создает коллекцию эффектов, которая будет применена к юниту
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @param bool $new
     * @return ActionCollection
     * @throws Exception
     */
    private function createEffectActions(
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        bool $new = false
    ): ActionCollection
    {
        if ($new || $this->actions === null) {

            $actionFactory = new ActionFactory();

            $data = [
                'type'           => ActionInterface::EFFECT,
                'action_unit'    => $this->unit,
                'enemy_command'  => $enemyCommand,
                'allies_command' => $alliesCommand,
                'type_target'    => ActionInterface::TARGET_SELF,
                'name'           => self::USE_MESSAGE,
                'effect'         => [
                    'name'                  => self::NAME,
                    'icon'                  => self::ICON,
                    'duration'              => self::DURATION,
                    'on_apply_actions'      => [
                        [
                            'type'           => ActionInterface::BUFF,
                            'action_unit'    => $this->unit,
                            'enemy_command'  => $enemyCommand,
                            'allies_command' => $alliesCommand,
                            'type_target'    => ActionInterface::TARGET_SELF,
                            'name'           => self::USE_MESSAGE,
                            'modify_method'  => self::MODIFY_METHOD,
                            'power'          => self::MODIFY_POWER,
                            'message_method' => ActionInterface::SKIP_MESSAGE_METHOD,
                        ],
                    ],
                    'on_next_round_actions' => [],
                    'on_disable_actions'    => [],
                ],
            ];

            $this->actions = new ActionCollection();
            $this->actions->add($actionFactory->create($data));
        }

        return $this->actions;
    }
}
