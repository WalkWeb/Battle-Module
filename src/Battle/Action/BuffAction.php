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
        int $typeTarget,
        string $name,
        string $modifyMethod,
        int $power
    )
    {
        parent::__construct($actionUnit, $enemyCommand, $alliesCommand, $typeTarget);
        $this->name = $name;
        $this->modifyMethod = $modifyMethod;
        $this->power = $power;
    }

    /**
     * @return string
     * @throws ActionException
     */
    public function handle(): string
    {
        $this->targetUnit = $this->searchTargetUnit();

        if (!$this->targetUnit) {
            throw new ActionException(ActionException::NO_TARGET_FOR_BUFF);
        }

        return $this->targetUnit->applyAction($this);
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

    public function getRevertAction(): ActionInterface
    {
        $rollbackAction = new BuffAction(
            $this->actionUnit,
            $this->enemyCommand,
            $this->alliesCommand,
            $this->typeTarget,
            $this->name,
            $this->modifyMethod . self::ROLLBACK_METHOD_SUFFIX,
            $this->power
        );

        $rollbackAction->setRevertValue($this->getRevertValue());

        return $rollbackAction;
    }

    public function setFactualPower(int $factualPower): void {}
}
