<?php

declare(strict_types=1);

namespace Battle\Unit\Ability\Damage;

use Battle\Action\ActionCollection;
use Battle\Action\DamageAction;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\AbstractAbility;
use Battle\Unit\UnitInterface;

class HeavyStrikeAbility extends AbstractAbility
{
    public const NAME           = 'Heavy Strike';
    public const ICON           = '/images/icons/ability/335.png';
    public const MESSAGE_METHOD = 'damageAbility';

    /**
     * Heavy Strike наносит 250% урона от базового урона юнита
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     */
    public function getAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        $collection = new ActionCollection();

        $collection->add(new DamageAction(
            $this->unit,
            $enemyCommand,
            $alliesCommand,
            DamageAction::TARGET_RANDOM_ENEMY,
            (int)($this->unit->getDamage() * 2.5),
            true,
            self::NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            self::MESSAGE_METHOD,
            self::ICON,
        ));

        return $collection;
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
     * Боевые способности считаем всегда доступными для применения - потому что если живых противников нет бой должен
     * остановиться
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return bool
     */
    public function canByUsed(CommandInterface $enemyCommand, CommandInterface $alliesCommand): bool
    {
        return true;
    }
}
