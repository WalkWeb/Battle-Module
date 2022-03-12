<?php

declare(strict_types=1);

namespace Battle\Unit\Ability\Summon;

use Battle\Unit\UnitInterface;

class SummonFireElementalAbility extends AbstractSummonAbility
{
    private const NAME                = 'Fire Elemental';
    private const ICON                = '/images/icons/ability/198.png';

    private const SUMMON_NAME         = 'Fire Elemental';
    private const SUMMON_LEVEL        = 3;
    private const SUMMON_AVATAR       = '/images/avas/summon/fire-elemental.png';
    private const SUMMON_DAMAGE       = 17;
    private const SUMMON_ATTACK_SPEED = 1.1;
    private const SUMMON_BLOCK        = 0;
    private const SUMMON_LIFE         = 62;
    private const SUMMON_MELEE        = true;
    private const SUMMON_RACE_ID      = 10;

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
        $this->ready = $unit->getRage() === UnitInterface::MAX_RAGE;
    }

    /**
     * Способность отмечает свое использование - переходит в неактивный статус и обнуляет концентрацию у юнита
     */
    public function usage(): void
    {
        $this->ready = false;
        $this->unit->useRageAbility();
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
