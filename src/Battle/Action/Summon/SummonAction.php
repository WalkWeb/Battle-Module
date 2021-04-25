<?php

declare(strict_types=1);

namespace Battle\Action\Summon;

use Battle\Action\AbstractAction;
use Battle\Classes\UnitClassFactory;
use Battle\Unit\Unit;
use Battle\Unit\UnitInterface;
use Exception;

class SummonAction extends AbstractAction
{
    protected const NAME          = 'summon Imp';
    protected const HANDLE_METHOD = 'applySummonAction';

    //
    private $name = 'Imp';
    private $url = '/images/avas/monsters/004.png';
    private $damage = 10;
    private $attackSpeed = 1;
    private $life = 30;
    private $melee = true;
    private $classId = 1;

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

    public function setFactualPower(int $factualPower)
    {
        return 0;
    }

    /**
     * @return UnitInterface
     * @throws Exception
     */
    public function getSummonUnit(): UnitInterface
    {
        return new Unit(
            $this->generateId(),
            $this->name,
            $this->url,
            $this->damage,
            $this->attackSpeed,
            $this->life,
            $this->life,
            $this->melee,
            UnitClassFactory::create($this->classId)
        );
    }

    /**
     * Генерация UUID не используется для лучшей производительности
     *
     * @param int|null $length
     * @return string
     * @throws Exception
     */
    private function generateId(?int $length = 5): string
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
