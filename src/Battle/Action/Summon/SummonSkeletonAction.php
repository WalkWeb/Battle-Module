<?php

declare(strict_types=1);

namespace Battle\Action\Summon;

use Battle\Unit\Race\RaceFactory;
use Battle\Unit\Unit;
use Battle\Unit\UnitInterface;
use Exception;

class SummonSkeletonAction extends SummonAction
{
    public const NAME = 'summon Skeleton';

    private $name = 'Skeleton';
    private $level = 1;
    private $url = '/images/avas/monsters/003.png';
    private $damage = 16;
    private $attackSpeed = 1;
    private $life = 38;
    private $melee = true;
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
                $this->actionUnit->getContainer()
            );
        }

        return $this->summonUnit;
    }

    /**
     * @return string
     */
    public function getNameAction(): string
    {
        return self::NAME;
    }
}
