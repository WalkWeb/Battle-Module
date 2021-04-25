<?php

declare(strict_types=1);

namespace Battle\Action\Heal;

use Battle\Action\AbstractAction;
use Battle\Chat\Message;

class HealAction extends AbstractAction
{
    protected const NAME          = 'heal';
    protected const HANDLE_METHOD = 'applyHealAction';

    public function getHandleMethod(): string
    {
        return self::HANDLE_METHOD;
    }

    /**
     * @return string
     */
    public function handle(): string
    {
        $this->targetUnit = $this->alliesCommand->getUnitForHeal();

        if (!$this->targetUnit) {
            return Message::hoTargetForHeal($this);
        }

        return $this->targetUnit->applyAction($this);
    }

    public function getPower(): int
    {
        // Базовое лечение в 120% от силы удара юнита
        return (int)round($this->getActionUnit()->getDamage() * 1.2);
    }

    public function setFactualPower(int $factualPower): void
    {
        $this->factualPower = $factualPower;
    }
}
