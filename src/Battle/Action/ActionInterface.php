<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Unit\UnitInterface;

interface ActionInterface
{
    public function handle(): string;
    public function getNameAction(): string;
    public function getActionUnit(): UnitInterface;
    public function getTargetUnit(): UnitInterface;
    public function getPower();
    public function setFactualPower(int $factualPower);
    public function getFactualPower();
}
