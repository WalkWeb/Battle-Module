<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Unit\Unit;

interface ActionInterface
{
    public function handle(): string;
    public function getNameAction(): string;
    public function getActionUnit(): Unit;
    public function getTargetUnit(): Unit;
    public function getPower();
    public function setFactualPower(int $factualPower);
    public function getFactualPower();
}
