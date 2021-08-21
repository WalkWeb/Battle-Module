<?php

declare(strict_types=1);

namespace Battle\Unit\Ability\Effect;

use Battle\Action\ActionCollection;
use Battle\Action\BuffAction;
use Battle\Action\EffectAction;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\AbstractAbility;
use Battle\Unit\Effect\Effect;
use Battle\Unit\Effect\EffectCollection;
use Battle\Unit\UnitInterface;

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

    public function getAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        return $this->createEffectActions($enemyCommand, $alliesCommand);
    }

    public function getName(): string
    {
        return self::NAME;
    }

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

    public function canByUsed(CommandInterface $enemyCommand, CommandInterface $alliesCommand): bool
    {
        foreach ($this->getAction($enemyCommand, $alliesCommand) as $action) {
            if (!$action->canByUsed()) {
                return false;
            }
        }

        return true;
    }

    private function createEffectActions(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        if ($this->actions === null) {
            // Создаем коллекцию событий (с одним бафом), которая будет применена к персонажу, при применении эффекта
            $onApplyActionCollection = new ActionCollection();

            $onApplyActionCollection->add(new BuffAction(
                $this->unit,
                $enemyCommand,
                $alliesCommand,
                self::USE_MESSAGE,
                self::MODIFY_METHOD,
                self::MODIFY_POWER
            ));

            // Создаем коллекцию эффектов, с одним эффектом при применении - Reserve Forces
            $effects = new EffectCollection();

            // Создаем сам эффект
            $effects->add(new Effect(
                self::NAME,
                self::ICON,
                self::DURATION,
                $onApplyActionCollection,
                new ActionCollection(),
                new ActionCollection()
            ));

            // Создаем коллекцию событий для применения к юниту
            $this->actions = new ActionCollection();

            $this->actions->add(new EffectAction(
                $this->unit,
                $enemyCommand,
                $alliesCommand,
                self::USE_MESSAGE,
                $effects
            ));
        }

        return $this->actions;
    }
}
