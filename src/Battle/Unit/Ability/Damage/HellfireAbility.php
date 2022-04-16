<?php

declare(strict_types=1);

namespace Battle\Unit\Ability\Damage;

use Battle\Action\ActionCollection;
use Battle\Action\DamageAction;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\AbstractAbility;
use Battle\Unit\UnitInterface;

class HellfireAbility extends AbstractAbility
{
    public const NAME           = 'Hellfire';
    public const ICON           = '/images/icons/ability/276.png';
    public const MESSAGE_METHOD = 'damageAbility';

    /**
     * Hellfire наносит 150% урона от базового урона юнита по всем живым противникам
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
            DamageAction::TARGET_ALL_ENEMY,
            (int)($this->unit->getOffense()->getDamage() * 1.5),
            true,
            self::NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            self::MESSAGE_METHOD,
            self::ICON,
        ));

        return $collection;
    }

    /**
     * Способность активируется при полной ярости юнита
     *
     * @param UnitInterface $unit
     */
    public function update(UnitInterface $unit): void
    {
        $this->ready = $unit->getRage() === UnitInterface::MAX_RAGE;
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
