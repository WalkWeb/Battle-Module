<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Container\ContainerInterface;
use Battle\Unit\UnitInterface;

/**
 * Важно отметить разницу между WaitAction и ParalysisAction
 *
 * Не смотря на то, что внешне это абсолютно аналогичные события - и в том и в том случае юнит просто пропускает ход,
 * в деталях они сильно отличаются: WaitAction это действие самого юнита, которое формируется когда скорость атаки ниже
 * 1, и с какой-то вероятностью он не совершает удар в своем ходе. А ParalysisAction это действие которое применяется к
 * юниту от эффекта, которое отмечает, что он походил, после чего ход юнит уже не совершает.
 *
 * @package Battle\Action
 */
class ParalysisAction extends AbstractAction
{
    private const HANDLE_METHOD           = 'applyParalysisAction';

    public const DEFAULT_ANIMATION_METHOD = 'wait';
    public const PARALYSIS_MESSAGE_METHOD = 'paralysis';
    public const STUN_MESSAGE_METHOD      = 'stunned';
    public const EFFECT_ANIMATION_METHOD  = 'effectDamage';

    /**
     * @var string
     */
    protected string $messageMethod;

    /**
     * В отличие от прочих событий, ParalysisAction всегда применяется к себе и не требует $typeTarget в конструктор
     *
     * @param ContainerInterface $container
     * @param UnitInterface $actionUnit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @param string $messageMethod
     */
    public function __construct(
        ContainerInterface $container,
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        string $messageMethod
    )
    {
        parent::__construct($container, $actionUnit, $enemyCommand, $alliesCommand, self::TARGET_SELF);

        $this->messageMethod = $messageMethod;
    }

    public function getHandleMethod(): string
    {
        return self::HANDLE_METHOD;
    }

    public function handle(): ActionCollection
    {
        $this->actionUnit->applyAction($this);
        return new ActionCollection();
    }

    public function getNameAction(): string
    {
        return '';
    }

    public function getAnimationMethod(): string
    {
        return self::DEFAULT_ANIMATION_METHOD;
    }

    public function getMessageMethod(): string
    {
        return $this->messageMethod;
    }

    /**
     * Паралич (т.е. пропуск хода юнитом) всегда доступен для использования – никаких ограничений нет
     *
     * @return bool
     */
    public function canByUsed(): bool
    {
        return true;
    }
}
