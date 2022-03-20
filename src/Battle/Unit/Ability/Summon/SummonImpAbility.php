<?php

declare(strict_types=1);

namespace Battle\Unit\Ability\Summon;

use Battle\Unit\UnitInterface;

class SummonImpAbility extends AbstractSummonAbility
{
    private const NAME                = 'Imp';
    private const ICON                = '/images/icons/ability/275.png';

    private const SUMMON_NAME         = 'Imp';
    private const SUMMON_LEVEL        = 1;
    private const SUMMON_AVATAR       = '/images/avas/monsters/004.png';
    private const SUMMON_DAMAGE       = 10;
    private const SUMMON_ATTACK_SPEED = 1;
    private const SUMMON_BLOCK        = 0;
    private const SUMMON_BLOCK_IGNORE = 0;
    private const SUMMON_LIFE         = 30;
    private const SUMMON_MELEE        = true;
    private const SUMMON_RACE_ID      = 9;

    public function __construct(UnitInterface $unit)
    {
        parent::__construct(
            $unit,
            self::SUMMON_NAME,
            self::SUMMON_LEVEL,
            self::SUMMON_AVATAR,
            self::SUMMON_DAMAGE,
            self::SUMMON_ATTACK_SPEED,
            self::SUMMON_BLOCK,
            self::SUMMON_BLOCK_IGNORE,
            self::SUMMON_LIFE,
            self::SUMMON_MELEE,
            self::SUMMON_RACE_ID,
            self::ICON
        );
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

    public function getName(): string
    {
        return self::NAME;
    }

    public function getIcon(): string
    {
        return self::ICON;
    }
}
