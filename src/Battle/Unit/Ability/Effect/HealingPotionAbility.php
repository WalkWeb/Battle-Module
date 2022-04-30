<?php

declare(strict_types=1);

namespace Battle\Unit\Ability\Effect;

use Battle\Action\ActionCollection;
use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Action\HealAction;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\AbstractAbility;
use Battle\Unit\UnitInterface;
use Exception;

class HealingPotionAbility extends AbstractAbility
{
    private const NAME           = 'Healing Potion';
    private const ICON           = '/images/icons/ability/234.png';
    private const DURATION       = 4;
    private const POWER          = 15;
    private const MESSAGE_METHOD = 'applyEffect';
    private const DISPOSABLE     = false;

    public function __construct(UnitInterface $unit)
    {
        parent::__construct($unit, self::DISPOSABLE);
    }

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
     * @param bool $testMode
     */
    public function update(UnitInterface $unit, bool $testMode = false): void
    {
        $this->ready = $unit->getConcentration() === UnitInterface::MAX_CONS;
    }

    /**
     * Способность отмечает свое использование - переходит в неактивный статус и обнуляет концентрацию у юнита
     */
    public function usage(): void
    {
        $this->ready = false;
        $this->usage = true;
        $this->unit->useConcentrationAbility();
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
        foreach ($this->getAction($enemyCommand, $alliesCommand) as $action) {
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
                'type_target'    => ActionInterface::TARGET_WOUNDED_ALLIES_EFFECT,
                'name'           => self::NAME,
                'icon'           => self::ICON,
                'message_method' => self::MESSAGE_METHOD,
                'effect'         => [
                    'name'                  => self::NAME,
                    'icon'                  => self::ICON,
                    'duration'              => self::DURATION,
                    'on_apply_actions'      => [],
                    'on_next_round_actions' => [
                        [
                            'type'             => ActionInterface::HEAL,
                            'action_unit'      => $this->unit,
                            'enemy_command'    => $enemyCommand,
                            'allies_command'   => $alliesCommand,
                            'type_target'      => ActionInterface::TARGET_SELF,
                            'name'             => self::NAME,
                            'power'            => self::POWER,
                            'animation_method' => HealAction::EFFECT_ANIMATION_METHOD,
                            'message_method'   => HealAction::EFFECT_MESSAGE_METHOD,
                            'icon'             => self::ICON,
                        ],
                    ],
                    'on_disable_actions'    => [],
                ],
            ];

            $this->actions = new ActionCollection();
            $this->actions->add($actionFactory->create($data));
        }

        return $this->actions;
    }
}
