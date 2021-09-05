<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;

class WaitAction extends AbstractAction
{
    private const NAME                     = 'preparing to attack';
    private const HANDLE_METHOD            = 'applyWaitAction';
    private const DEFAULT_ANIMATION_METHOD = 'wait';

    /**
     * В отличие от прочих событий, WaitAction всегда применяется к себе и не требует $typeTarget в конструктор
     *
     * @param UnitInterface $actionUnit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     */
    public function __construct(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand
    )
    {
        parent::__construct($actionUnit, $enemyCommand, $alliesCommand, self::TARGET_SELF);
    }

    public function getHandleMethod(): string
    {
        return self::HANDLE_METHOD;
    }

    public function handle(): string
    {
        return $this->actionUnit->applyAction($this);
    }

    public function getNameAction(): string
    {
        return self::NAME;
    }

    public function getAnimationMethod(): string
    {
        return self::DEFAULT_ANIMATION_METHOD;
    }

    public function setFactualPower(int $factualPower): void {}
}
