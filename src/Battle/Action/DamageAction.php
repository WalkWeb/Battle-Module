<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Container\ContainerInterface;
use Battle\Unit\Offense\OffenseInterface;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;

class DamageAction extends AbstractAction
{
    public const HANDLE_METHOD           = 'applyDamageAction';
    public const DEFAULT_NAME            = 'attack';
    public const UNIT_ANIMATION_METHOD   = 'damage';
    public const EFFECT_ANIMATION_METHOD = 'effectDamage';
    public const DEFAULT_MESSAGE_METHOD  = 'damage';
    public const EFFECT_MESSAGE_METHOD   = 'effectDamage';

    /**
     * @var OffenseInterface
     */
    protected OffenseInterface $offense;

    /**
     * @var bool
     */
    protected bool $canBeAvoided;

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var string
     */
    protected string $animationMethod;

    /**
     * @var string
     */
    protected string $messageMethod;

    /**
     * Было ли событие заблокировано
     *
     * Данные хранятся в виде массива:
     * [
     *   'unit_id1' => true,
     *   'unit_id2' => true,
     * ]
     *
     * Примечание: для простоты кода можно было бы хранить просто id юнитов: ['unit_id1', 'unit_id2'], и проверять через
     * in_array(), но это создавало бы пространство для ошибки, когда один и тот же юнит как бы заблокировал один удар
     * дважды, и сообщение в чат о таком DamageAction сформировалось бы некорректно. Используемый же формат чуть менее
     * оптимален с точки зрения кода, но избавляет от возможности такой ошибки
     *
     * @var array
     */
    protected array $blockedByUnit = [];

    /**
     * Аналогично с blockedByUnit, только для уклонившихся юнитов
     *
     * @var array
     */
    protected array $dodgedByUnit = [];

    public function __construct(
        ContainerInterface $container,
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        int $typeTarget,
        OffenseInterface $offense,
        bool $canBeAvoided,
        string $name,
        string $animationMethod,
        string $messageMethod,
        string $icon = ''
    )
    {
        parent::__construct($container, $actionUnit, $enemyCommand, $alliesCommand, $typeTarget, $icon);
        $this->offense = $offense;
        $this->canBeAvoided = $canBeAvoided;
        $this->name = $name;
        $this->animationMethod = $animationMethod;
        $this->messageMethod = $messageMethod;
    }

    /**
     * @return string
     */
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

    /**
     * @return OffenseInterface
     */
    public function getOffense(): OffenseInterface
    {
        return $this->offense;
    }

    /**
     * @param UnitInterface $unit
     * @param int $factualPower
     */
    public function addFactualPower(UnitInterface $unit, int $factualPower): void
    {
        $this->factualPower += $factualPower;
        $this->factualPowerByUnit[$unit->getId()] = $factualPower;
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

    /**
     * @return string
     */
    public function getNameAction(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getAnimationMethod(): string
    {
        return $this->animationMethod;
    }

    /**
     * @return string
     */
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

    /**
     * @param UnitInterface $unit
     * @return bool
     */
    public function isDodged(UnitInterface $unit): bool
    {
        if (!array_key_exists($unit->getId(), $this->dodgedByUnit)) {
            return false;
        }

        return (bool)$this->dodgedByUnit[$unit->getId()];
    }

    /**
     * @param UnitInterface $unit
     */
    public function dodged(UnitInterface $unit): void
    {
        $this->dodgedByUnit[$unit->getId()] = true;
    }

    /**
     * @return bool
     */
    public function isCanBeAvoided(): bool
    {
        return $this->canBeAvoided;
    }
}
