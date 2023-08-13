<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Container\ContainerInterface;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;

class BuffAction extends AbstractAction
{
    public const MAX_LIFE                = 'multiplierMaxLife';
    public const PHYSICAL_DAMAGE         = 'multiplierPhysicalDamage';
    public const FIRE_DAMAGE             = 'multiplierFireDamage';
    public const WATER_DAMAGE            = 'multiplierWaterDamage';
    public const AIR_DAMAGE              = 'multiplierAirDamage';
    public const EARTH_DAMAGE            = 'multiplierEarthDamage';
    public const LIFE_DAMAGE             = 'multiplierLifeDamage';
    public const DEATH_DAMAGE            = 'multiplierDeathDamage';
    public const ATTACK_SPEED            = 'multiplierAttackSpeed';
    public const ACCURACY                = 'multiplierAccuracy';
    public const MAGIC_ACCURACY          = 'multiplierMagicAccuracy';
    public const DEFENSE                 = 'multiplierDefense';
    public const MAGIC_DEFENSE           = 'multiplierMagicDefense';
    public const CRITICAL_CHANCE         = 'multiplierCriticalChance';
    public const CRITICAL_MULTIPLIER     = 'multiplierCriticalMultiplier';
    public const ADD_BLOCK               = 'addBlock';
    public const ADD_PHYSICAL_RESIST     = 'addPhysicalResist';
    public const ADD_FIRE_RESIST         = 'addFireResist';
    public const ADD_WATER_RESIST        = 'addWaterResist';
    public const ADD_AIR_RESIST          = 'addAirResist';
    public const ADD_EARTH_RESIST        = 'addEarthResist';
    public const ADD_LIFE_RESIST         = 'addLifeResist';
    public const ADD_DEATH_RESIST        = 'addDeathResist';
    public const ADD_PHYSICAL_MAX_RESIST = 'addPhysicalMaxResist';
    public const ADD_FIRE_MAX_RESIST     = 'addFireMaxResist';
    public const ADD_WATER_MAX_RESIST    = 'addWaterMaxResist';
    public const ADD_AIR_MAX_RESIST      = 'addAirMaxResist';

    private const HANDLE_METHOD          = 'applyBuffAction';
    private const DEFAULT_MESSAGE_METHOD = 'buff';

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
    private $revertValue;

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
    )
    {
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

    public function setRevertValue($revertValue): void
    {
        $this->revertValue = $revertValue;
    }

    /**
     * @return float|int
     */
    public function getRevertValue()
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
