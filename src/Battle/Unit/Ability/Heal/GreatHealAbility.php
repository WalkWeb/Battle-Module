<?php

declare(strict_types=1);

namespace Battle\Unit\Ability\Heal;

use Battle\Action\ActionCollection;
use Battle\Action\HealAction;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\AbstractAbility;
use Battle\Unit\UnitInterface;

class GreatHealAbility extends AbstractAbility
{
    private const NAME = 'Great Heal';
    private const ICON = '/images/icons/ability/196.png';

    /**
     * @var ActionCollection
     */
    private $actionCollection;

    /**
     * Great Heal лечение в 300% от силы удара юнита
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     */
    public function getAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        if ($this->actionCollection === null) {
            $this->actionCollection = new ActionCollection();

            $this->actionCollection->add(new HealAction(
                $this->unit,
                $enemyCommand,
                $alliesCommand,
                HealAction::TARGET_WOUNDED_ALLIES,
                $this->unit->getDamage() * 3,
                self::NAME,
                HealAction::UNIT_ANIMATION_METHOD,
                HealAction::ABILITY_MESSAGE_METHOD,
                self::ICON
            ));
        }

        return $this->actionCollection;
    }

    /**
     * Способность активируется при полной концентрации юнита
     *
     * @param UnitInterface $unit
     */
    public function update(UnitInterface $unit): void
    {
        $this->ready = $unit->getConcentration() === UnitInterface::MAX_CONS;
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
     * Проверяет, есть ли цель для лечения
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
