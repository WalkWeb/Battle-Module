<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;

class ParalysisAction extends AbstractAction
{
    private const HANDLE_METHOD           = 'applyParalysisAction';
    public const DEFAULT_ANIMATION_METHOD = 'wait';
    public const DEFAULT_MESSAGE_METHOD   = 'paralysis';

    /**
     * В отличие от прочих событий, ParalysisAction всегда применяется к себе и не требует $typeTarget в конструктор
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

    public function handle(): void
    {
        $this->actionUnit->applyAction($this);
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
        return self::DEFAULT_MESSAGE_METHOD;
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
