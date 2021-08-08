<?php

declare(strict_types=1);

namespace Battle\Action;

class WaitAction extends AbstractAction
{
    private const NAME          = 'preparing to attack';
    private const HANDLE_METHOD = 'applyWaitAction';

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
