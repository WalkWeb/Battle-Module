<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;

class BuffAction extends AbstractAction
{
    // TODO Подумать над реализацией revert-сообщения для чата

    private const HANDLE_METHOD = 'applyBuffAction';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $modifyMethod;

    /**
     * @var int
     */
    private $power;

    /**
     * @var int
     */
    private $revertValue;

    public function __construct(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        string $name,
        string $modifyMethod,
        int $power
    )
    {
        parent::__construct($actionUnit, $enemyCommand, $alliesCommand);
        $this->name = $name;
        $this->modifyMethod = $modifyMethod;
        $this->power = $power;
    }


    public function handle(): string
    {
        return $this->actionUnit->applyAction($this);
    }

    public function getPower(): int
    {
        return $this->power;
    }

    public function getHandleMethod(): string
    {
        return self::HANDLE_METHOD;
    }

    public function getNameAction(): string
    {
        return $this->name;
    }

    public function getModifyMethod(): string
    {
        return $this->modifyMethod;
    }

    public function setRevertValue(int $revertValue): void
    {
        $this->revertValue = $revertValue;
    }

    public function getRevertValue(): int
    {
        return $this->revertValue;
    }

    public function setFactualPower(int $factualPower): void {}
}
