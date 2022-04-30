<?php

declare(strict_types=1);

namespace Battle\Unit\Ability\Heal;

use Battle\Action\ActionCollection;
use Battle\Action\HealAction;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\AbstractAbility;
use Battle\Unit\UnitInterface;

class GeneralHealAbility extends AbstractAbility
{
    private const NAME       = 'General Heal';
    private const ICON       = '/images/icons/ability/452.png';
    private const DISPOSABLE = false;

    public function __construct(UnitInterface $unit)
    {
        parent::__construct($unit, self::DISPOSABLE);
    }

    /**
     * General Heal - лечение всей команды на 120% от силы удара юнита
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     */
    public function getAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        $collection = new ActionCollection();

        $collection->add(new HealAction(
            $this->unit,
            $enemyCommand,
            $alliesCommand,
            HealAction::TARGET_ALL_WOUNDED_ALLIES,
            (int)($this->unit->getOffense()->getDamage() * 1.2),
            self::NAME,
            HealAction::UNIT_ANIMATION_METHOD,
            HealAction::ABILITY_MESSAGE_METHOD,
            self::ICON
        ));

        return $collection;
    }

    /**
     * Способность активируется при полной ярости юнита
     *
     * @param UnitInterface $unit
     * @param bool $testMode
     */
    public function update(UnitInterface $unit, bool $testMode = false): void
    {
        $this->ready = $unit->getRage() === UnitInterface::MAX_RAGE;
    }

    /**
     * Способность отмечает свое использование - переходит в неактивный статус и обнуляет ярость у юнита
     */
    public function usage(): void
    {
        $this->ready = false;
        $this->usage = true;
        $this->unit->useRageAbility();
    }

    /**
     * Проверяет, есть ли цели для лечения
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return bool
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
}
