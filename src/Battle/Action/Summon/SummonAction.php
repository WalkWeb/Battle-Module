<?php

declare(strict_types=1);

namespace Battle\Action\Summon;

use Battle\Action\AbstractAction;
use Battle\Unit\UnitInterface;
use Exception;

abstract class SummonAction extends AbstractAction
{
    protected const HANDLE_METHOD = 'applySummonAction';

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
    public function setFactualPower(int $factualPower)
    {
        return 0;
    }

    /**
     * Генерация UUID не используется для лучшей производительности
     *
     * @param int|null $length
     * @return string
     * @throws Exception
     */
    protected function generateId(?int $length = 5): string
    {
        $chars = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $numChars = strlen($chars);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= $chars[random_int(1, $numChars) - 1];
        }
        return $string;
    }
}
