<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;

class BuffAction extends AbstractAction
{
    private const HANDLE_METHOD            = 'applyBuffAction';
    private const DEFAULT_ANIMATION_METHOD = 'skip';
    private const DEFAULT_MESSAGE_METHOD   = 'buff';

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

    /**
     * @var string
     */
    private $messageMethod;

    public function __construct(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        int $typeTarget,
        string $name,
        string $modifyMethod,
        int $power,
        ?string $messageMethod = null
    )
    {
        parent::__construct($actionUnit, $enemyCommand, $alliesCommand, $typeTarget);
        $this->name = $name;
        $this->modifyMethod = $modifyMethod;
        $this->power = $power;
        $this->messageMethod = $messageMethod ?? self::DEFAULT_MESSAGE_METHOD;
    }

    /**
     * @throws ActionException
     * @throws UnitException
     */
    public function handle(): void
    {
        $this->targetUnits = $this->searchTargetUnits($this);

        if (count($this->targetUnits) === 0) {
            throw new ActionException(ActionException::NO_TARGET_FOR_BUFF);
        }

        foreach ($this->targetUnits as $targetUnit) {
            $targetUnit->applyAction($this);
        }
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

    public function setRevertValue($revertValue): void
    {
        $this->revertValue = $revertValue;
    }

    public function getRevertValue()
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

    public function getAnimationMethod(): string
    {
        return self::DEFAULT_ANIMATION_METHOD;
    }

    public function getMessageMethod(): string
    {
        return $this->messageMethod;
    }

    /**
     * Бафф всегда может примениться, потому что проверка на возможность применения того или иного бафа происходит в
     * EffectAction
     *
     * @return bool
     */
    public function canByUsed(): bool
    {
        return true;
    }

    /**
     * У баффов нет силы действия - соответственно метод ничего не делает
     *
     * @param string $unitId
     * @param int $factualPower
     */
    public function addFactualPower(string $unitId, int $factualPower): void {}
}
