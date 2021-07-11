<?php

declare(strict_types=1);

namespace Battle\Action\Heal;

use Battle\Action\AbstractAction;
use Battle\Command\CommandInterface;
use Battle\Result\Chat\Message;
use Battle\Unit\UnitInterface;

class HealAction extends AbstractAction
{
    protected const NAME          = 'heal';
    protected const HANDLE_METHOD = 'applyHealAction';

    /**
     * @var int
     */
    protected $power;

    /**
     * @var string
     */
    protected $name;

    public function __construct(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        Message $message,
        ?int $power = null,
        ?string $name = null
    )
    {
        parent::__construct($actionUnit, $enemyCommand, $alliesCommand, $message);
        $this->power = $power ?? (int)($actionUnit->getDamage() * 1.2);
        $this->name = $name ?? self::NAME;
    }

    public function getHandleMethod(): string
    {
        return self::HANDLE_METHOD;
    }

    /**
     * Если лечить некого - совершается обычная атака
     *
     * @return string
     */
    public function handle(): string
    {
        $this->targetUnit = $this->alliesCommand->getUnitForHeal();

        if (!$this->targetUnit) {
            $this->actionUnit->upMaxConcentration();
            // $action->successHandle = false
            return self::NO_HANDLE_MESSAGE;
        }

        $this->successHandle = true;

        return $this->targetUnit->applyAction($this);
    }

    public function getPower(): int
    {
        return $this->power;
    }

    public function setFactualPower(int $factualPower): void
    {
        $this->factualPower = $factualPower;
    }

    /**
     * @return string
     */
    public function getNameAction(): string
    {
        return $this->name;
    }
}
