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

class ReserveForcesAbility extends AbstractAbility
{
    private const NAME          = 'Reserve Forces';
    private const ICON          = '/images/icons/ability/156.png';
    private const USE_MESSAGE   = 'use Reserve Forces';
    private const DURATION      = 6;
    private const MODIFY_METHOD = 'multiplierMaxLife';
    private const MODIFY_POWER  = 130;

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
     * Способность активируется при полной концентрации юнита
     *
     * @param UnitInterface $unit
     */
    public function update(UnitInterface $unit): void
    {
        if (!$this->ready && $unit->getConcentration() === UnitInterface::MAX_CONS) {
            $this->ready = true;
        }
    }

    /**
     * Способность отмечает свое использование - переходит в неактивный статус и обнуляет концентрацию у юнита
     */
    public function usage(): void
    {
        $this->ready = false;
        $this->unit->useConcentrationAbility();
    }

    /**
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
     * При создании коллекции событий для применения способности с эффектом есть несколько особенностей:
     *
     * 1. Создавать всю коллекцию каждый раз, при вызове getAction() или canByUsed() - это долго
     * 2. Если создать коллекцию один раз, и возвращать только её, то при повторном использовании способности она
     *    вернет эффект с длительностью 0 (потому что эффект уже закончился при предыдущем применении)
     *
     * По этому добавлен параметр $new - чтобы при вызове getAction() коллекция событий со всеми эффектами внутри
     * создавалась каждый раз новой, а при вызове canByUsed() - создавалась только один раз, а дальше возвращался
     * существующий
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
                'effects'        => [
                    [
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
                            ],
                        ],
                        'on_next_round_actions' => [],
                        'on_disable_actions'    => [],
                    ],
                ],
            ];

            $this->actions = new ActionCollection();
            $this->actions->add($actionFactory->create($data));
        }

        return $this->actions;
    }
}
