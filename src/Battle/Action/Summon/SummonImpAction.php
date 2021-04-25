<?php

declare(strict_types=1);

namespace Battle\Action\Summon;

use Battle\Classes\UnitClassFactory;
use Battle\Unit\Unit;
use Battle\Unit\UnitInterface;
use Exception;

class SummonImpAction extends SummonAction
{
    public const NAME = 'summon Imp';

    private $name = 'Imp';
    private $url = '/images/avas/monsters/004.png';
    private $damage = 10;
    private $attackSpeed = 1;
    private $life = 30;
    private $melee = true;
    private $classId = 1;

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
     * @return string
     */
    public function getNameAction(): string
    {
        return self::NAME;
    }
}
