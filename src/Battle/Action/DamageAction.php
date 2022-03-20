<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;

class DamageAction extends AbstractAction
{
    private const HANDLE_METHOD          = 'applyDamageAction';
    private const DEFAULT_NAME           = 'attack';
    public const UNIT_ANIMATION_METHOD   = 'damage';
    public const EFFECT_ANIMATION_METHOD = 'effectDamage';
    public const DEFAULT_MESSAGE_METHOD  = 'damage';
    public const EFFECT_MESSAGE_METHOD   = 'effectDamage';

    /**
     * @var int
     */
    protected $damage;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $animationMethod;

    /**
     * @var string
     */
    protected $messageMethod;

    /**
     * Было ли событие заблокировано
     *
     * Данные хранятся в виде массива:
     * unit_id => true
     *
     * @var bool
     */
    protected $blockedByUnit = [];

    public function __construct(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        int $typeTarget,
        int $damage,
        ?string $name = null,
        ?string $animationMethod = null,
        ?string $messageMethod = null,
        string $icon = ''
    )
    {
        parent::__construct($actionUnit, $enemyCommand, $alliesCommand, $typeTarget, $icon);
        $this->damage = $damage;
        $this->name = $name ?? self::DEFAULT_NAME;
        $this->animationMethod = $animationMethod ?? self::UNIT_ANIMATION_METHOD;
        $this->messageMethod = $messageMethod ?? self::DEFAULT_MESSAGE_METHOD;
    }

    public function getHandleMethod(): string
    {
        return self::HANDLE_METHOD;
    }

    /**
     * @throws ActionException
     * @throws UnitException
     */
    public function handle(): void
    {
        if (!$this->enemyCommand->isAlive()) {
            throw new ActionException(ActionException::NO_DEFINED);
        }

        $this->targetUnits = $this->searchTargetUnits($this);

        if (count($this->targetUnits) === 0) {
            throw new ActionException(ActionException::NO_DEFINED_AGAIN);
        }

        foreach ($this->targetUnits as $targetUnit) {
            $targetUnit->applyAction($this);
        }
    }

    public function getPower(): int
    {
        return $this->damage;
    }

    public function addFactualPower(string $unitId, int $factualPower): void
    {
        $this->factualPower += $factualPower;
        $this->factualPowerByUnit[$unitId] = $factualPower;
    }

    /**
     * @param UnitInterface $unit
     * @return int
     * @throws ActionException
     */
    public function getFactualPowerByUnit(UnitInterface $unit): int
    {
        if (!array_key_exists($unit->getId(), $this->factualPowerByUnit)) {
            throw new ActionException(ActionException::NO_POWER_BY_UNIT . ': ' . $unit->getId());
        }

        return $this->factualPowerByUnit[$unit->getId()];
    }

    public function getNameAction(): string
    {
        return $this->name;
    }

    public function getAnimationMethod(): string
    {
        return $this->animationMethod;
    }

    public function getMessageMethod(): string
    {
        return $this->messageMethod;
    }

    /**
     * Урон по умолчанию считается доступным для использования - потому что:
     *
     * 1. Если это атака юнита - а живых противников нет, то бой должен был остановиться (т.е. ошибка в Round)
     * 2. Если это урон от эффекта - в Stroke делается проверка на то, живой ли юнит после применение урона
     *
     * @return bool
     */
    public function canByUsed(): bool
    {
        return true;
    }

    /**
     * @param UnitInterface $unit
     * @return bool
     */
    public function isBlocked(UnitInterface $unit): bool
    {
        if (!array_key_exists($unit->getId(), $this->blockedByUnit)) {
            return false;
        }

        return (bool)$this->blockedByUnit[$unit->getId()];
    }

    /**
     * @param UnitInterface $unit
     */
    public function blocked(UnitInterface $unit): void
    {
        $this->blockedByUnit[$unit->getId()] = true;
    }
}
