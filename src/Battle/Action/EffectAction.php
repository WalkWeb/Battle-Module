<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Container\ContainerInterface;
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
    private string $name;

    /**
     * @var string
     */
    protected string $animationMethod;

    /**
     * @var string
     */
    protected string $messageMethod;

    /**
     * @var EffectInterface
     */
    private EffectInterface $effect;

    public function __construct(
        ContainerInterface $container,
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
        parent::__construct($container, $actionUnit, $enemyCommand, $alliesCommand, $typeTarget, $icon);
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
        if ($this->targetUnits === null || count($this->targetUnits) === 0) {
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

    /**
     * Так как один и тот же EffectAction может применяться к нескольким юнитам, чтобы каждый из них имел свой
     * уникальный эффект - необходимо клонировать возвращаемый объект
     *
     * @return EffectInterface
     */
    public function getEffect(): EffectInterface
    {
        return clone $this->effect;
    }

    /**
     * Логика будет разной для применения эффекта на всю вражескую команду, всю свою команду, и на одну цель
     *
     * @return bool
     * @throws ActionException
     * @throws UnitException
     */
    public function canByUsed(): bool
    {
        // Если эффект применяется на всю вражескую команду
        if ($this->typeTarget === self::TARGET_ALL_ENEMY) {

            // Цели для такого события - все живые противники
            // Это необходимо для того, чтобы юниты с имеющимся эффектом все равно получили эффект и обновили его длительность
            $this->targetUnits = $this->enemyCommand->getAllAliveUnits();

            // Но проверка на необходимость применения события делается по наличию юнитов без эффекта (хотя бы одного)
            $targets = $this->enemyCommand->getUnitsForEffect($this->effect);

            // Т.е. проверяется использования способности по хотя бы одной цели без эффекта, а применяется уже событие
            // по всем живым целям в команде

            return count($targets) > 0;
        }

        // TODO Эффект на всю свою команду - пока нет такого эффекта, чтобы покрыть данный код тестами

        // Если эффект применяется на одну цель
        $this->targetUnits = $this->searchTargetUnits($this);

        foreach ($this->targetUnits as $targetUnit) {
            if ($targetUnit->isAlive() && !$targetUnit->getEffects()->exist($this->effect)) {
                return true;
            }
        }

        return false;
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
