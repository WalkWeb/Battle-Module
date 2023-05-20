<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Container\ContainerInterface;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;

class HealAction extends AbstractAction
{
    private const NAME                   = 'heal';
    private const HANDLE_METHOD          = 'applyHealAction';
    public const UNIT_ANIMATION_METHOD   = 'heal';
    public const EFFECT_ANIMATION_METHOD = 'effectHeal';
    public const DEFAULT_MESSAGE_METHOD  = 'heal';
    public const EFFECT_MESSAGE_METHOD   = 'effectHeal';

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
        string $icon = '',
        bool $targetTracking = true
    )
    {
        parent::__construct($container, $actionUnit, $enemyCommand, $alliesCommand, $typeTarget, $icon, $targetTracking);

        $this->power = $power;
        $this->name = $name ?? self::NAME;
        $this->animationMethod = $animationMethod;
        $this->messageMethod = $messageMethod;
    }

    public function getHandleMethod(): string
    {
        return self::HANDLE_METHOD;
    }

    /**
     * Выбирает цель для лечения и лечит её
     *
     * @throws ActionException
     * @throws UnitException
     */
    public function handle(): ActionCollection
    {
        $this->targetUnits = $this->searchTargetUnits($this);

        // Такой ситуации быть не должно, потому возможность применения события должна проверяться до её применения
        if (count($this->targetUnits) === 0) {
            throw new ActionException(ActionException::NO_TARGET_FOR_HEAL);
        }

        foreach ($this->targetUnits as $targetUnit) {
            $targetUnit->applyAction($this);
        }

        return new ActionCollection();
    }

    public function getPower(): int
    {
        return $this->power;
    }

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
     * Вариант применения эффект на противников не рассматривается - т.к. помогать противоположной команде не
     * подразумевается
     *
     * @return bool
     */
    public function canByUsed(): bool
    {
        if ($this->typeTarget === self::TARGET_SELF && $this->actionUnit->getLife() === $this->actionUnit->getTotalLife()) {
            return false;
        }

        return (bool)$this->alliesCommand->getUnitForHeal();
    }

    public function getAnimationMethod(): string
    {
        return $this->animationMethod;
    }

    public function getMessageMethod(): string
    {
        return $this->messageMethod;
    }
}
