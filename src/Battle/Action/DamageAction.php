<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;

class DamageAction extends AbstractAction
{
    private const HANDLE_METHOD          = 'applyDamageAction';
    private const DEFAULT_NAME           = 'attack';
    public const UNIT_ANIMATION_METHOD   = 'damage';
    public const EFFECT_ANIMATION_METHOD = 'effectDamage';
    private const DEFAULT_MESSAGE_METHOD = 'damage';
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

    public function __construct(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        int $typeTarget,
        ?int $damage = null,
        ?string $name = null,
        ?string $animationMethod = null,
        ?string $messageMethod = null
    )
    {
        parent::__construct($actionUnit, $enemyCommand, $alliesCommand, $typeTarget);
        $this->damage = $damage ?? $actionUnit->getDamage();
        $this->name = $name ?? self::DEFAULT_NAME;
        $this->animationMethod = $animationMethod ?? self::UNIT_ANIMATION_METHOD;
        $this->messageMethod = $messageMethod ?? self::DEFAULT_MESSAGE_METHOD;
    }

    public function getHandleMethod(): string
    {
        return self::HANDLE_METHOD;
    }

    /**
     * @return string
     * @throws ActionException
     */
    public function handle(): string
    {
        if (!$this->enemyCommand->isAlive()) {
            throw new ActionException(ActionException::NO_DEFINED);
        }

        $this->targetUnit = $this->searchTargetUnit($this);

        if (!$this->targetUnit) {
            throw new ActionException(ActionException::NO_DEFINED_AGAIN);
        }

        return $this->targetUnit->applyAction($this);
    }

    public function getPower(): int
    {
        return $this->damage;
    }

    public function setFactualPower(int $factualPower): void
    {
        $this->factualPower = $factualPower;
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
}
