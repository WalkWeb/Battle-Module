<?php

declare(strict_types=1);

namespace Battle\Unit\Ability\Summon;

use Battle\Action\ActionCollection;
use Battle\Action\SummonAction;
use Battle\Command\CommandInterface;
use Battle\Traits\IdTrait;
use Battle\Unit\Ability\AbstractAbility;
use Battle\Unit\Race\RaceFactory;
use Battle\Unit\Unit;
use Battle\Unit\UnitInterface;
use Exception;

class SummonImpAbility extends AbstractAbility
{
    use IdTrait;

    public const ACTION_NAME          = 'summon Imp';

    private const SUMMON_NAME         = 'Imp';
    private const SUMMON_LEVEL        = 1;
    private const SUMMON_AVATAR       = '/images/avas/monsters/004.png';
    private const SUMMON_DAMAGE       = 10;
    private const SUMMON_ATTACK_SPEED = 1;
    private const SUMMON_LIFE         = 30;
    private const SUMMON_MELEE        = true;
    private const SUMMON_RACE_ID      = 9;

    /**
     * Призывает беса
     *
     * TODO Метод полностью дублируется в каждой SummonAbility - подумать, как убрать дубли
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws Exception
     */
    public function getAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        $collection = new ActionCollection();

        $collection->add(new SummonAction(
            $this->unit,
            $enemyCommand,
            $alliesCommand,
            $this->container->getMessage(),
            self::ACTION_NAME,
            new Unit(
                self::generateId(),
                self::SUMMON_NAME,
                self::SUMMON_LEVEL,
                self::SUMMON_AVATAR,
                self::SUMMON_DAMAGE,
                self::SUMMON_ATTACK_SPEED,
                self::SUMMON_LIFE,
                self::SUMMON_LIFE,
                self::SUMMON_MELEE,
                $this->unit->getCommand(),
                RaceFactory::create(self::SUMMON_RACE_ID),
                $this->unit->getContainer()
            )
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
}