<?php

declare(strict_types=1);

namespace Battle\Unit\Ability\Heal;

use Battle\Action\ActionCollection;
use Battle\Action\Heal\HealAction;
use Battle\Command\CommandInterface;
use Battle\Container\ContainerException;
use Battle\Unit\Ability\AbstractAbility;
use Battle\Unit\UnitInterface;

class GreatHealAbility extends AbstractAbility
{
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
     * @throws ContainerException
     */
    public function getAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        if ($this->actionCollection === null) {
            $this->actionCollection = new ActionCollection();

            $this->actionCollection->add(new HealAction(
                $this->unit,
                $enemyCommand,
                $alliesCommand,
                $this->container->getMessage(),
                $this->unit->getDamage() * 3,
                'use Great Heal and heal'
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
     * Проверяет, есть ли цель для лечения
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return bool
     * @throws ContainerException
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
}
