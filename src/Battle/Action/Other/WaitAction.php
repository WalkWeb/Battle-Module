<?php

declare(strict_types=1);

namespace Battle\Action\Other;

use Battle\Action\AbstractAction;

class WaitAction extends AbstractAction
{
    protected const NAME          = 'preparing to attack';
    protected const HANDLE_METHOD = 'applyWaitAction';

    public function getHandleMethod(): string
    {
        return self::HANDLE_METHOD;
    }

    public function handle(): string
    {
        return $this->actionUnit->applyAction($this);
    }

    public function getNameAction(): string
    {
        return self::NAME;
    }

    public function setFactualPower(int $factualPower): void {}
}
