<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Container\ContainerInterface;
use Battle\Unit\Offense\MultipleOffense\MultipleOffenseInterface;
use Battle\Unit\Offense\Offense;
use Battle\Unit\Offense\OffenseInterface;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;
use Exception;

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
     * @var bool
     */
    protected bool $criticalDamage;

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

    /**
     * Восстановленное здоровье от вампиризма
     *
     * @var int
     */
    protected int $restoreLifeFromVampirism = 0;

    /**
     * Восстановленная мага от магического вампиризма
     *
     * @var int
     */
    protected int $restoreManaFromMagicVampirism = 0;

    /**
     * Случайный множитель урона. От 0.5 до 1.6
     *
     * @var float
     */
    protected float $damageMultiplier = 1.0;

    /**
     * Необходимо ли рандомизировать урон от этого события
     *
     * @var bool
     */
    protected bool $randomDamage;

    /**
     * @param ContainerInterface $container
     * @param UnitInterface $actionUnit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @param int $typeTarget
     * @param bool $canBeAvoided
     * @param string $name
     * @param string $animationMethod
     * @param string $messageMethod
     * @param OffenseInterface|null $offense
     * @param MultipleOffenseInterface|null $multipleOffense
     * @param string $icon
     * @param bool $targetTracking
     * @param bool $randomDamage
     * @throws Exception
     */
    public function __construct(
        ContainerInterface $container,
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        int $typeTarget,
        bool $canBeAvoided,
        string $name,
        string $animationMethod,
        string $messageMethod,
        OffenseInterface $offense = null,
        MultipleOffenseInterface $multipleOffense = null,
        string $icon = '',
        bool $targetTracking = true,
        bool $randomDamage = true
    )
    {
        parent::__construct($container, $actionUnit, $enemyCommand, $alliesCommand, $typeTarget, $icon, $targetTracking);

        if (is_null($offense)) {
            if (is_null($multipleOffense)) {
                throw new ActionException(ActionException::EMPTY_OFFENSE_AND_MULTIPLE);
            }

            $this->offense = $this->createMultipleOffense($actionUnit->getOffense(), $multipleOffense);

        } else {
            $this->offense = $offense;
        }

        $this->canBeAvoided = $canBeAvoided;
        $this->name = $name;
        $this->animationMethod = $animationMethod;
        $this->messageMethod = $messageMethod;
        $this->randomDamage = $randomDamage;
        $this->calculateCriticalDamage();
        $this->calculateDamageMultiplier();
    }

    /**
     * @return string
     */
    public function getHandleMethod(): string
    {
        return self::HANDLE_METHOD;
    }

    /**
     * Вычисляет цель (цели) для нанесения урона
     *
     * Наносит им урон
     *
     * Если атакующий юнит имеет вампиризм - восстанавливает атакующему здоровье
     *
     * @throws ActionException
     * @throws UnitException
     */
    public function handle(): ActionCollection
    {
        $callbackActions = new ActionCollection();

        if (!$this->enemyCommand->isAlive()) {
            throw new ActionException(ActionException::NO_DEFINED);
        }

        $this->targetUnits = $this->searchTargetUnits($this);

        if (count($this->targetUnits) === 0) {
            throw new ActionException(ActionException::NO_DEFINED_AGAIN);
        }

        foreach ($this->targetUnits as $targetUnit) {
            $callbackActions->addCollection($targetUnit->applyAction($this));
        }

        if ($this->factualPower > 0) {
            if ($this->offense->getVampirism() > 0) {

                $this->restoreLifeFromVampirism = (int)($this->factualPower * ($this->offense->getVampirism() / 100));

                // Не смотря на наличие вампиризма и нанесенного урона - его может быть недостаточно для восстановления
                // хотя бы единицы жизни. По этому дополнительно проверяем, что есть что восстанавливать
                if ($this->restoreLifeFromVampirism > 0) {
                    $this->actionUnit->applyAction(new HealAction(
                        $this->container,
                        $this->actionUnit,
                        $this->enemyCommand,
                        $this->alliesCommand,
                        HealAction::TARGET_SELF,
                        $this->restoreLifeFromVampirism,
                        '',
                        HealAction::SKIP_ANIMATION_METHOD,
                        HealAction::SKIP_MESSAGE_METHOD
                    ));
                }
            }

            if ($this->offense->getMagicVampirism() > 0) {

                // Тоже самое с магическим вампиризмом
                $this->restoreManaFromMagicVampirism = (int)($this->factualPower * ($this->offense->getMagicVampirism() / 100));

                if ($this->restoreManaFromMagicVampirism > 0) {
                    $this->actionUnit->applyAction(new ManaRestoreAction(
                        $this->container,
                        $this->actionUnit,
                        $this->enemyCommand,
                        $this->alliesCommand,
                        HealAction::TARGET_SELF,
                        $this->restoreManaFromMagicVampirism,
                        '',
                        HealAction::SKIP_ANIMATION_METHOD,
                        HealAction::SKIP_MESSAGE_METHOD
                    ));
                }
            }
        }

        return $callbackActions;
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
    public function isEvaded(UnitInterface $unit): bool
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

    /**
     * @return bool
     */
    public function isCriticalDamage(): bool
    {
        return $this->criticalDamage;
    }

    /**
     * @return int
     */
    public function getRestoreLifeFromVampirism(): int
    {
        return $this->restoreLifeFromVampirism;
    }

    /**
     * @return int
     */
    public function getRestoreManaFromMagicVampirism(): int
    {
        return $this->restoreManaFromMagicVampirism;
    }

    /**
     * @return float
     */
    public function getRandomDamageMultiplier(): float
    {
        return $this->damageMultiplier;
    }

    /**
     * @param float $damageMultiplier
     */
    public function setRandomDamageMultiplier(float $damageMultiplier): void
    {
        $this->damageMultiplier = $damageMultiplier;
    }

    /**
     * @return bool
     */
    public function isRandomDamage(): bool
    {
        return $this->randomDamage;
    }

    /**
     * В режиме тестов шанс критического удара считается не случайно, а округляется
     *
     * @throws Exception
     */
    private function calculateCriticalDamage(): void
    {
        if ($this->container->isTestMode()) {
            $this->criticalDamage = (bool)(int)round($this->offense->getCriticalChance() / 100);
        } else {
            $this->criticalDamage = $this->offense->getCriticalChance() > random_int(0, 100);
        }
    }

    /**
     * В режиме тестов случайной множитель урона не применяется
     *
     * @throws Exception
     */
    private function calculateDamageMultiplier(): void
    {
        if (!$this->container->isTestMode()) {
            $this->damageMultiplier = random_int(5, 16) * 0.1;
        }
    }

    /**
     * @param OffenseInterface $offense
     * @param MultipleOffenseInterface $multipleOffense
     * @return OffenseInterface
     * @throws Exception
     */
    private function createMultipleOffense(
        OffenseInterface $offense,
        MultipleOffenseInterface $multipleOffense
    ): OffenseInterface
    {
        $resultOffense = new Offense(
            $this->container,
            $offense->getDamageType(),
            $offense->getWeaponType()->getId(),
            (int)($offense->getPhysicalDamage() * $multipleOffense->getDamageMultiplier()),
            (int)($offense->getFireDamage() * $multipleOffense->getDamageMultiplier()),
            (int)($offense->getWaterDamage() * $multipleOffense->getDamageMultiplier()),
            (int)($offense->getAirDamage() * $multipleOffense->getDamageMultiplier()),
            (int)($offense->getEarthDamage() * $multipleOffense->getDamageMultiplier()),
            (int)($offense->getLifeDamage() * $multipleOffense->getDamageMultiplier()),
            (int)($offense->getDeathDamage() * $multipleOffense->getDamageMultiplier()),
            round($offense->getAttackSpeed() * $multipleOffense->getSpeedMultiplier(), 2),
            round($offense->getCastSpeed() * $multipleOffense->getSpeedMultiplier(), 2),
            (int)($offense->getAccuracy() * $multipleOffense->getAccuracyMultiplier()),
            (int)($offense->getMagicAccuracy() * $multipleOffense->getAccuracyMultiplier()),
            $multipleOffense->getBlockIgnoring() > 0 ? $multipleOffense->getBlockIgnoring() : $offense->getBlockIgnoring(),
            (int)($offense->getCriticalChance() * $multipleOffense->getCriticalChanceMultiplier()),
            (int)($offense->getCriticalMultiplier() * $multipleOffense->getCriticalMultiplierMultiplier()),
            $offense->getDamageMultiplier(),
            $multipleOffense->getVampirism() > 0 ? $multipleOffense->getVampirism() : $offense->getVampirism(),
            $offense->getMagicVampirism()
        );

        if ($multipleOffense->getDamageConvert()) {
            $resultOffense->convertDamage($multipleOffense->getDamageConvert());
        }

        return $resultOffense;
    }
}
