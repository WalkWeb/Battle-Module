<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Result\Chat\Message;
use Battle\Unit\UnitInterface;

abstract class AbstractAction implements ActionInterface
{
    /**
     * @var UnitInterface
     */
    protected $actionUnit;

    /**
     * @var UnitInterface
     */
    protected $targetUnit;

    /**
     * @var CommandInterface
     */
    protected $alliesCommand;

    /**
     * @var CommandInterface
     */
    protected $enemyCommand;

    /**
     * @var Message
     */
    protected $message;

    /**
     * @var int
     */
    protected $factualPower = 0;

    /**
     * Был ли успешно применен Action
     *
     * @var bool
     */
    protected $successHandle = false;

    public function __construct(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        Message $message
    )
    {
        $this->actionUnit = $actionUnit;
        $this->enemyCommand = $enemyCommand;
        $this->alliesCommand = $alliesCommand;
        $this->message = $message;
    }

    public function getActionUnit(): UnitInterface
    {
        return $this->actionUnit;
    }

    /**
     * @return UnitInterface
     * @throws ActionException
     */
    public function getTargetUnit(): UnitInterface
    {
        if ($this->targetUnit === null) {
            throw new ActionException(ActionException::NO_TARGET_UNIT);
        }

        return $this->targetUnit;
    }

    /**
     * @return int
     * @throws ActionException
     */
    public function getPower(): int
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    public function getFactualPower(): int
    {
        return $this->factualPower;
    }

    /**
     * Сейчас всего 4 типа событий, и 3 из них могут примениться по-умолчанию, это:
     *
     * 1. Урон (если все противники умерли - бой заканчивается)
     * 2. Пропуск хода
     * 3. Призыв
     *
     * И только для одного события, нужно делать проверку наличия цели, это:
     *
     * 4. Лечение
     *
     * Соответственно внутри самого HealAction данный метод переназначается
     *
     * @return bool
     */
    public function canByUsed(): bool
    {
        return true;
    }
}
