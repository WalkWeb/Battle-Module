<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Container\ContainerInterface;
use Battle\Unit\UnitCollection;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;

class ManaRestoreAction extends AbstractAction
{
    public const NAME                    = 'restore mana';
    private const HANDLE_METHOD          = 'applyManaRestoreAction';
    public const DEFAULT_MESSAGE_METHOD  = 'manaRestore';

    /**
     * @var int
     */
    protected int $power;

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
     * @param ContainerInterface $container
     * @param UnitInterface $actionUnit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @param int $typeTarget
     * @param int $power
     * @param string $name
     * @param string $animationMethod
     * @param string $messageMethod
     * @param string $icon
     * @throws ActionException
     */
    public function __construct(
        ContainerInterface $container,
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        int $typeTarget,
        int $power,
        string $name,
        string $animationMethod,
        string $messageMethod,
        string $icon = ''
    )
    {
        parent::__construct($container, $actionUnit, $enemyCommand, $alliesCommand, $typeTarget, $icon);

        if ($this->typeTarget !== self::TARGET_SELF) {
            throw new ActionException(ActionException::INVALID_MANA_RESTORE_TARGET);
        }

        $this->power = $power;
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
     * TODO На данный момент ManaRestoreAction применяется только в механике магического вампиризма, соответственно
     * TODO Action применяется только к себе
     */
    public function handle(): ActionCollection
    {
        $this->actionUnit->applyAction($this);
        return new ActionCollection();
    }

    /**
     * @return int
     */
    public function getPower(): int
    {
        return $this->power;
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
     * Восстановление маны считается всегда доступным для использования. Если мана полная - то просто не будет ничего
     * восстановлено
     *
     * @return bool
     */
    public function canByUsed(): bool
    {
        return true;
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
     * TODO Пока только на себя
     *
     * @return UnitCollection
     * @throws UnitException
     */
    public function getTargetUnits(): UnitCollection
    {
        $units = new UnitCollection();
        $units->add($this->actionUnit);
        return $units;
    }
}
