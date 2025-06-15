<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Container\ContainerInterface;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;

class BuffAction extends AbstractAction
{
    public const string MAX_LIFE                = 'multiplierMaxLife';
    public const string MAX_MANA                = 'multiplierMaxMana';
    public const string ADD_DAMAGE_MULTIPLIER   = 'addDamageMultiplier';
    public const string PHYSICAL_DAMAGE         = 'multiplierPhysicalDamage';
    public const string FIRE_DAMAGE             = 'multiplierFireDamage';
    public const string WATER_DAMAGE            = 'multiplierWaterDamage';
    public const string AIR_DAMAGE              = 'multiplierAirDamage';
    public const string EARTH_DAMAGE            = 'multiplierEarthDamage';
    public const string LIFE_DAMAGE             = 'multiplierLifeDamage';
    public const string DEATH_DAMAGE            = 'multiplierDeathDamage';
    public const string ATTACK_SPEED            = 'multiplierAttackSpeed';
    public const string CAST_SPEED              = 'multiplierCastSpeed';
    public const string ACCURACY                = 'multiplierAccuracy';
    public const string MAGIC_ACCURACY          = 'multiplierMagicAccuracy';
    public const string DEFENSE                 = 'multiplierDefense';
    public const string MAGIC_DEFENSE           = 'multiplierMagicDefense';
    public const string CRITICAL_CHANCE         = 'multiplierCriticalChance';
    public const string ADD_CRITICAL_CHANCE     = 'addCriticalChance';
    public const string ADD_CRITICAL_MULTIPLIER = 'addCriticalMultiplier';
    public const string ADD_BLOCK               = 'addBlock';
    public const string ADD_MAGIC_BLOCK         = 'addMagicBlock';
    public const string ADD_BLOCK_IGNORE        = 'addBlockIgnore';
    public const string ADD_VAMPIRISM           = 'addVampirism';
    public const string ADD_MAGIC_VAMPIRISM     = 'addMagicVampirism';
    public const string ADD_PHYSICAL_RESIST     = 'addPhysicalResist';
    public const string ADD_FIRE_RESIST         = 'addFireResist';
    public const string ADD_WATER_RESIST        = 'addWaterResist';
    public const string ADD_AIR_RESIST          = 'addAirResist';
    public const string ADD_EARTH_RESIST        = 'addEarthResist';
    public const string ADD_LIFE_RESIST         = 'addLifeResist';
    public const string ADD_DEATH_RESIST        = 'addDeathResist';
    public const string ADD_PHYSICAL_MAX_RESIST = 'addPhysicalMaxResist';
    public const string ADD_FIRE_MAX_RESIST     = 'addFireMaxResist';
    public const string ADD_WATER_MAX_RESIST    = 'addWaterMaxResist';
    public const string ADD_AIR_MAX_RESIST      = 'addAirMaxResist';
    public const string ADD_EARTH_MAX_RESIST    = 'addEarthMaxResist';
    public const string ADD_LIFE_MAX_RESIST     = 'addLifeMaxResist';
    public const string ADD_DEATH_MAX_RESIST    = 'addDeathMaxResist';
    public const string ADD_GLOBAL_RESIST       = 'addGlobalResist';
    public const string ADD_MENTAL_BARRIER      = 'addMentalBarrier';
    public const string ADD_CONCENTRATION       = 'addMultiplierConcentration';
    public const string ADD_CUNNING             = 'addMultiplierCunning';
    public const string ADD_RAGE                = 'addMultiplierRage';

    private const string HANDLE_METHOD          = 'applyBuffAction';
    private const string DEFAULT_MESSAGE_METHOD = 'buff';

    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $modifyMethod;

    /**
     * @var int
     */
    private int $power;

    /**
     * @var float|int
     */
    private float|int $revertValue = 0;

    /**
     * @var string
     */
    private string $messageMethod;

    public function __construct(
        ContainerInterface $container,
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        int $typeTarget,
        string $name,
        string $modifyMethod,
        int $power,
        ?string $messageMethod = null
    ) {
        parent::__construct($container, $actionUnit, $enemyCommand, $alliesCommand, $typeTarget);
        $this->name = $name;
        $this->modifyMethod = $modifyMethod;
        $this->power = $power;
        $this->messageMethod = $messageMethod ?? self::DEFAULT_MESSAGE_METHOD;
    }

    /**
     * @throws ActionException
     * @throws UnitException
     */
    public function handle(): ActionCollection
    {
        $this->targetUnits = $this->searchTargetUnits($this);

        if (count($this->targetUnits) === 0) {
            throw new ActionException(ActionException::NO_TARGET_FOR_BUFF);
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

    public function getHandleMethod(): string
    {
        return self::HANDLE_METHOD;
    }

    public function getNameAction(): string
    {
        return $this->name;
    }

    public function getModifyMethod(): string
    {
        return $this->modifyMethod;
    }

    public function setRevertValue(float|int $revertValue): void
    {
        $this->revertValue = $revertValue;
    }

    /**
     * @return float|int
     */
    public function getRevertValue(): float|int
    {
        return $this->revertValue;
    }

    public function getRevertAction(): ActionInterface
    {
        $rollbackAction = new BuffAction(
            $this->container,
            $this->actionUnit,
            $this->enemyCommand,
            $this->alliesCommand,
            $this->typeTarget,
            $this->name,
            $this->modifyMethod . self::ROLLBACK_METHOD_SUFFIX,
            $this->power,
            self::SKIP_MESSAGE_METHOD
        );

        $rollbackAction->setRevertValue($this->getRevertValue());

        return $rollbackAction;
    }

    public function getAnimationMethod(): string
    {
        return self::SKIP_ANIMATION_METHOD;
    }

    public function getMessageMethod(): string
    {
        return $this->messageMethod;
    }

    /**
     * Бафф всегда может примениться, потому что проверка на возможность применения того или иного бафа происходит в
     * EffectAction
     *
     * @return bool
     */
    public function canByUsed(): bool
    {
        return true;
    }
}
