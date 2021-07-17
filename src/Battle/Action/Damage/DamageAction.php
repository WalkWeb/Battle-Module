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

    /**
     * @var string
     */
    protected $name;

    public function __construct(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        Message $message,
        ?int $damage = null,
        ?string $name = null
    )
    {
        parent::__construct($actionUnit, $enemyCommand, $alliesCommand, $message);
        $this->damage = $damage ?? $actionUnit->getDamage();
        $this->name = $name ?? self::NAME;
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
        return $this->name;
    }
}
