<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Unit\Effect\EffectInterface;
use Battle\Unit\UnitException;
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
        ?string $animationMethod = null,
        ?string $messageMethod = null
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
     * @throws ActionException
     */
    public function handle(): void
    {
        if (count($this->targetUnits) === 0) {
            throw new ActionException(ActionException::NO_TARGET_FOR_EFFECT);
        }

        foreach ($this->targetUnits as $targetUnit) {
            $targetUnit->applyAction($this);
        }
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
     * @throws UnitException
     */
    public function canByUsed(): bool
    {
        $this->targetUnits = $this->searchTargetUnits($this);

        if (count($this->targetUnits) === 0) {
            return false;
        }

        // Action может быть использован если есть хотя бы одна цель для использования
        // Как происходит проверка - проходим по всем юнитам, если эффект есть - добавляем в массив 0
        // Если эффекта нет - добавляем 1
        // И в финале проверяем сумму значений на больше 0 - если больше - значит есть цели для эффекта
        $effectSum = [];

        foreach ($this->targetUnits as $targetUnit) {
            $effectSum[] = $targetUnit->getEffects()->exist($this->effect) ? 0 : 1;
        }

        return array_sum($effectSum) > 0;
    }

    public function getAnimationMethod(): string
    {
        return $this->animationMethod;
    }

    public function getMessageMethod(): string
    {
        return $this->messageMethod;
    }

    /**
     * У эффекта нет силы действия - соответственно метод ничего не делает
     *
     * @param string $unitId
     * @param int $factualPower
     */
    public function addFactualPower(string $unitId, int $factualPower): void {}
}
