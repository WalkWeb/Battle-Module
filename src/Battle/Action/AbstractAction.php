<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Container\ContainerInterface;
use Battle\Unit\Effect\EffectInterface;
use Battle\Unit\Offense\OffenseInterface;
use Battle\Unit\UnitCollection;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;

abstract class AbstractAction implements ActionInterface
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var UnitInterface
     */
    protected UnitInterface $creatorUnit;

    /**
     * @var UnitInterface
     */
    protected UnitInterface $actionUnit;

    /**
     * @var UnitCollection|null
     */
    protected ?UnitCollection $targetUnits = null;

    /**
     * @var CommandInterface
     */
    protected CommandInterface $alliesCommand;

    /**
     * @var CommandInterface
     */
    protected CommandInterface $enemyCommand;

    /**
     * Тип выбора цели для применения события, например: на себя, на врага, на самого раненого союзника и т.д.
     *
     * @var int
     */
    protected int $typeTarget;

    /**
     * Фактическая сила действия события. Актуально для урона и лечения - где подсчитывается суммарный урон или лечение
     *
     * @var int
     */
    protected int $factualPower = 0;

    /**
     * Фактическая сила действия отдельно по каждому юниту, в формате:
     *
     * [
     *   '354e4360-69f1-407e-bee3-de9cecceee55' => 10,
     *   'de8995de-35f1-450e-a56b-929ac08dc929' => 30,
     * ]
     *
     * @var array
     */
    protected array $factualPowerByUnit = [];

    /**
     * @var string
     */
    protected string $icon;

    protected bool $targetTracking;

    public function __construct(
        ContainerInterface $container,
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        int $typeTarget,
        string $icon = '',
        bool $targetTracking = true
    )
    {
        $this->container = $container;
        // По умолчанию creatorUnit равен actionUnit, и только у Action от эффектов actionUnit меняется когда эффект
        // применяется к цели, а creatorUnit остается неизменным
        $this->creatorUnit = $actionUnit;
        $this->actionUnit = $actionUnit;
        $this->enemyCommand = $enemyCommand;
        $this->alliesCommand = $alliesCommand;
        $this->typeTarget = $typeTarget;
        $this->icon = $icon;
        $this->targetTracking = $targetTracking;
    }

    public function getCreatorUnit(): UnitInterface
    {
        return $this->creatorUnit;
    }

    public function getActionUnit(): UnitInterface
    {
        return $this->actionUnit;
    }

    public function getTypeTarget(): int
    {
        return $this->typeTarget;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @return UnitCollection
     * @throws ActionException
     */
    public function getTargetUnits(): UnitCollection
    {
        if ($this->targetUnits === null || count($this->targetUnits) === 0) {
            throw new ActionException(ActionException::NO_TARGET_UNIT);
        }

        return $this->targetUnits;
    }

    /**
     * @return int
     * @throws ActionException
     */
    public function getPower(): int
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    /**
     * @return OffenseInterface
     * @throws ActionException
     */
    public function getOffense(): OffenseInterface
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    public function clearFactualPower(): void
    {
        $this->factualPower = 0;
    }

    public function getFactualPower(): int
    {
        return $this->factualPower;
    }

    /**
     * Во всех события, кроме урона и лечения (и воскрешения, кторое также лечит) нет силы действия - соответственно по
     * умолчанию этот метод ничего не делает, и только в DamageAction, HealAction и ResurrectionAction этот метод
     * подменяется
     *
     * @param UnitInterface $unit
     * @param int $factualPower
     */
    public function addFactualPower(UnitInterface $unit, int $factualPower): void {}

    /**
     * @param UnitInterface $unit
     * @return int
     * @throws ActionException
     */
    public function getFactualPowerByUnit(UnitInterface $unit): int
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    /**
     * @return UnitInterface
     * @throws ActionException
     */
    public function getSummonUnit(): UnitInterface
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    /**
     * @return string
     * @throws ActionException
     */
    public function getModifyMethod(): string
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    /**
     * @param int|float $revertValue
     * @throws ActionException
     */
    public function setRevertValue($revertValue): void
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    /**
     * @throws ActionException
     */
    public function getRevertValue()
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    /**
     * @return ActionInterface
     * @throws ActionException
     */
    public function getRevertAction(): ActionInterface
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    /**
     * @return EffectInterface
     * @throws ActionException
     */
    public function getEffect(): EffectInterface
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    /**
     * @param UnitInterface $unit
     */
    public function changeActionUnit(UnitInterface $unit): void
    {
        $this->actionUnit = $unit;
    }

    /**
     * Фактическая проверка на блок актуальна только для DamageAction, для всех остальных событий просто возвращается
     * false
     *
     * @param UnitInterface $unit
     * @return bool
     */
    public function isBlocked(UnitInterface $unit): bool
    {
        return false;
    }

    /**
     * @param UnitInterface $unit
     * @throws ActionException
     */
    public function blocked(UnitInterface $unit): void
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    /**
     * Фактическая проверка на уклонение актуальна только для DamageAction, для всех остальных событий просто
     * возвращается false
     *
     * @param UnitInterface $unit
     * @return bool
     */
    public function isEvaded(UnitInterface $unit): bool
    {
        return false;
    }

    /**
     * @param UnitInterface $unit
     * @throws ActionException
     */
    public function dodged(UnitInterface $unit): void
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    /**
     * @return bool
     * @throws ActionException
     */
    public function isCanBeAvoided(): bool
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    /**
     * @return bool
     * @throws ActionException
     */
    public function isCriticalDamage(): bool
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    /**
     * @return int
     * @throws ActionException
     */
    public function getRestoreLifeFromVampirism(): int
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    /**
     * @return int
     * @throws ActionException
     */
    public function getRestoreManaFromMagicVampirism(): int
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    /**
     * @return float
     * @throws ActionException
     */
    public function getRandomDamageMultiplier(): float
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    /**
     * @param float $damageMultiplier
     * @throws ActionException
     */
    public function setRandomDamageMultiplier(float $damageMultiplier): void
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    /**
     * @return bool
     * @throws ActionException
     */
    public function isRandomDamage(): bool
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    /**
     * @return CommandInterface
     */
    public function getAlliesCommand(): CommandInterface
    {
        return $this->alliesCommand;
    }

    /**
     * @return CommandInterface
     */
    public function getEnemyCommand(): CommandInterface
    {
        return $this->enemyCommand;
    }

    public function isTargetTracking(): bool
    {
        return $this->targetTracking;
    }

    /**
     * Ищет юнита для применения события.
     *
     * @param ActionInterface $action
     * @return UnitCollection
     * @throws ActionException
     * @throws UnitException
     */
    protected function searchTargetUnits(ActionInterface $action): UnitCollection
    {
        $units = new UnitCollection();

        switch ($this->typeTarget) {
            case self::TARGET_SELF:
                $units->add($this->actionUnit);
                return $units;
            case self::TARGET_RANDOM_ENEMY:
                if ($unit = $this->getRandomEnemyUnit()) {
                    $units->add($unit);
                }
                return $units;
            case self::TARGET_WOUNDED_ALLIES:
                if ($unit = $this->alliesCommand->getUnitForHeal()) {
                    $units->add($unit);
                }
                return $units;
            case self::TARGET_EFFECT_ENEMY:
                if ($unit = $this->enemyCommand->getUnitForEffect($action->getEffect())) {
                    $units->add($unit);
                }
                return $units;
            case self::TARGET_EFFECT_ALLIES:
                if ($unit = $this->alliesCommand->getUnitForEffect($action->getEffect())) {
                    $units->add($unit);
                }
                return $units;
            case self::TARGET_WOUNDED_ALLIES_EFFECT:
                if ($unit = $this->alliesCommand->getUnitForEffectHeal($action->getEffect())) {
                    $units->add($unit);
                }
                return $units;
            case self::TARGET_DEAD_ALLIES:
                if ($unit = $this->alliesCommand->getUnitForResurrection()) {
                    $units->add($unit);
                }
                return $units;
            case self::TARGET_ALL_ENEMY:
                return $this->enemyCommand->getAllAliveUnits();
                // TODO Из-за особенностей логики в EffectAction этот вариант по сути не используется
                // TODO Возможно имеет смысл и вовсе удалить
            case self::TARGET_ALL_WOUNDED_ALLIES:
                return $this->alliesCommand->getAllWoundedUnits();
            case self::TARGET_LAST_ALIVE_TARGETS:
                foreach ($this->actionUnit->getLastTargets() as $unit) {
                    if ($unit->isAlive()) {
                        $units->add($unit);
                    }
                }
                return $units;
            case self::TARGET_WOUNDED_SELF:
                if ($this->actionUnit->getLife() < $this->actionUnit->getTotalLife()) {
                    $units->add($this->actionUnit);
                }
                return $units;
        }

        throw new ActionException(ActionException::UNKNOWN_TYPE_TARGET . ': ' . $this->typeTarget);
    }

    /**
     * @return UnitInterface|null
     */
    private function getRandomEnemyUnit(): ?UnitInterface
    {
        if ((!$this->actionUnit->isMelee()) || ($this->actionUnit->isMelee() && !$this->enemyCommand->existMeleeUnits())) {
            return $this->enemyCommand->getUnitForAttacks();
        }

        return $this->enemyCommand->getMeleeUnitForAttacks();
    }
}
