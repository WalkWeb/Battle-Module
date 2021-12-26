<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Unit\Effect\EffectInterface;
use Battle\Unit\UnitInterface;

class EffectAction extends AbstractAction
{
    private const HANDLE_METHOD            = 'applyEffectAction';
    public const DEFAULT_ANIMATION_METHOD = 'effect';
    public const DEFAULT_MESSAGE_METHOD   = 'applyEffect';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    protected $animationMethod;

    /**
     * @var string
     */
    protected $messageMethod;

    /**
     * @var EffectInterface
     */
    private $effect;

    public function __construct(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        int $typeTarget,
        string $name,
        string $icon,
        EffectInterface $effect,
        string $animationMethod = null,
        string $messageMethod = null
    )
    {
        parent::__construct($actionUnit, $enemyCommand, $alliesCommand, $typeTarget, $icon);
        $this->name = $name;
        $this->effect = $effect;
        $this->animationMethod = $animationMethod ?? self::DEFAULT_ANIMATION_METHOD;
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
        if (!$this->targetUnit) {
            throw new ActionException(ActionException::NO_TARGET_FOR_EFFECT);
        }

        return $this->targetUnit->applyAction($this);
    }

    public function getNameAction(): string
    {
        return $this->name;
    }

    public function getEffect(): EffectInterface
    {
        return $this->effect;
    }

    /**
     * Проверки специально сделаны двумя отдельными if, чтобы покрытие тестов проверяло покрытиями тестами два варианта
     *
     * @return bool
     * @throws ActionException
     */
    public function canByUsed(): bool
    {
        $this->targetUnit = $this->searchTargetUnit($this);

        if (!$this->targetUnit) {
            return false;
        }

        if ($this->targetUnit->getEffects()->exist($this->effect)) {
            return false;
        }

        return true;
    }

    public function getAnimationMethod(): string
    {
        return $this->animationMethod;
    }

    public function getMessageMethod(): string
    {
        return $this->messageMethod;
    }

    public function setFactualPower(int $factualPower): void {}
}
