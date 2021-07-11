<?php

declare(strict_types=1);

namespace Battle\Action\Damage;

use Battle\Action\AbstractAction;
use Battle\Action\ActionException;
use Battle\Command\CommandInterface;
use Battle\Result\Chat\Message;
use Battle\Unit\UnitInterface;

class DamageAction extends AbstractAction
{
    protected const NAME          = 'attack';
    protected const HANDLE_METHOD = 'applyDamageAction';

    /**
     * @var int
     */
    protected $damage;

    public function __construct(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        Message $message,
        ?int $damage = null
    )
    {
        parent::__construct($actionUnit, $enemyCommand, $alliesCommand, $message);
        $this->damage = $damage ?? $actionUnit->getDamage();
    }

    public function getHandleMethod(): string
    {
        return self::HANDLE_METHOD;
    }

    /**
     * @return string
     * @throws ActionException
     */
    public function handle(): string
    {
        if (!$this->enemyCommand->isAlive()) {
            throw new ActionException(ActionException::NO_DEFINED);
        }

        $this->targetUnit = $this->getDefinedUnit();

        if (!$this->targetUnit) {
            throw new ActionException(ActionException::NO_DEFINED_AGAIN);
        }

        $this->successHandle = true;

        return $this->targetUnit->applyAction($this);
    }

    public function getPower(): int
    {
        return $this->damage;
    }

    public function setFactualPower(int $factualPower): void
    {
        $this->factualPower = $factualPower;
    }

    private function getDefinedUnit(): ?UnitInterface
    {
        if ((!$this->actionUnit->isMelee()) || ($this->actionUnit->isMelee() && !$this->enemyCommand->existMeleeUnits())) {
            return $this->enemyCommand->getUnitForAttacks();
        }

        return $this->enemyCommand->getMeleeUnitForAttacks();
    }

    public function getNameAction(): string
    {
        return static::NAME;
    }
}
