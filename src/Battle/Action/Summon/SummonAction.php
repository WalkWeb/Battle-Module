<?php

declare(strict_types=1);

namespace Battle\Action\Summon;

use Battle\Action\AbstractAction;
use Battle\Traits\IdTrait;
use Battle\Unit\UnitInterface;
use Exception;

abstract class SummonAction extends AbstractAction
{
    use IdTrait;

    protected const HANDLE_METHOD = 'applySummonAction';

    /**
     * @var UnitInterface
     */
    protected $summonUnit;

    public function getHandleMethod(): string
    {
        return self::HANDLE_METHOD;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function handle(): string
    {
        $unit = $this->getSummonUnit();
        $this->alliesCommand->getUnits()->add($unit);
        $this->successHandle = true;
        return $this->actionUnit->applyAction($this);
    }

    /**
     * @return UnitInterface
     */
    abstract public function getSummonUnit(): UnitInterface;

    /**
     * @param int $factualPower
     * @return int|mixed
     */
    public function setFactualPower(int $factualPower): void {}
}
