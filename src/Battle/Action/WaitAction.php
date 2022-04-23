<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;

/**
 * WaitAction это событие пропуска хода юнитом. Пропуск хода может быть в следующих вариантах:
 *
 * 1. Скорость атаки меньше 1, например 0.7, соответственно с 30% шансом юнит не совершит атаки в свой ход – и
 *    выполнится WaitAction
 * 2. Юнит оглушен или обездвижен каким-то другим способом – в этом случае также будет выполнен WaitAction
 *
 * @package Battle\Action
 */
class WaitAction extends AbstractAction
{
    private const NAME                     = 'preparing to attack';
    private const HANDLE_METHOD            = 'applyWaitAction';
    private const DEFAULT_ANIMATION_METHOD = 'wait';
    private const DEFAULT_MESSAGE_METHOD   = 'wait';

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

    public function handle(): void
    {
        $this->actionUnit->applyAction($this);
    }

    public function getNameAction(): string
    {
        return self::NAME;
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
     * Пропуск хода всегда доступен для использования – никаких ограничений нет
     *
     * @return bool
     */
    public function canByUsed(): bool
    {
        return true;
    }
}
