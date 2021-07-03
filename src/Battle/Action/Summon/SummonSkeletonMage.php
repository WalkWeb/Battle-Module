<?php

declare(strict_types=1);

namespace Battle\Action\Summon;

use Battle\Unit\Race\RaceFactory;
use Battle\Unit\Unit;
use Battle\Unit\UnitInterface;
use Exception;

class SummonSkeletonMage extends SummonAction
{
    public const NAME = 'summon Skeleton Mage';

    private $name = 'Skeleton Mage';
    private $level = 2;
    private $url = '/images/avas/monsters/008.png';
    private $damage = 13;
    private $attackSpeed = 1.2;
    private $life = 42;
    private $melee = false;
    private $raceId = 8;

    /**
     * @return UnitInterface
     * @throws Exception
     */
    public function getSummonUnit(): UnitInterface
    {
        if ($this->summonUnit === null) {
            $this->summonUnit = new Unit(
                self::generateId(),
                $this->name,
                $this->level,
                $this->url,
                $this->damage,
                $this->attackSpeed,
                $this->life,
                $this->life,
                $this->melee,
                $this->actionUnit->getCommand(),
                RaceFactory::create($this->raceId),
                $this->message
            );
        }

        return $this->summonUnit;
    }

    public function getNameAction(): string
    {
        return self::NAME;
    }
}
