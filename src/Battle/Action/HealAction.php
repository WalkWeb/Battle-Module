<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;

class HealAction extends AbstractAction
{
    private const NAME                   = 'heal';
    private const HANDLE_METHOD          = 'applyHealAction';
    public const UNIT_ANIMATION_METHOD   = 'heal';
    public const EFFECT_ANIMATION_METHOD = 'effectHeal';

    /**
     * @var int
     */
    protected $power;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $animationMethod;

    public function __construct(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        int $typeTarget,
        ?int $power = null,
        ?string $name = null,
        ?string $animationMethod = null
    )
    {
        parent::__construct($actionUnit, $enemyCommand, $alliesCommand, $typeTarget);

        $this->power = $power ?? $actionUnit->getDamage();
        $this->name = $name ?? self::NAME;
        $this->animationMethod = $animationMethod ?? self::UNIT_ANIMATION_METHOD;
    }

    public function getHandleMethod(): string
    {
        return self::HANDLE_METHOD;
    }

    /**
     * Если лечить некого - совершается обычная атака
     *
     * @return string
     * @throws ActionException
     */
    public function handle(): string
    {
        $this->targetUnit = $this->searchTargetUnit();

        // Такой ситуации быть не должно, потому возможность применения события должна проверяться до её применения
        if (!$this->targetUnit) {
            throw new ActionException(ActionException::NO_TARGET_FOR_HEAL);
        }

        return $this->targetUnit->applyAction($this);
    }

    public function getPower(): int
    {
        return $this->power;
    }

    public function setFactualPower(int $factualPower): void
    {
        $this->factualPower = $factualPower;
    }

    /**
     * @return string
     */
    public function getNameAction(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function canByUsed(): bool
    {
        return (bool)$this->alliesCommand->getUnitForHeal();
    }

    public function getAnimationMethod(): string
    {
        return $this->animationMethod;
    }
}
