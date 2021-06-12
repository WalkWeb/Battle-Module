<?php

declare(strict_types=1);

namespace Battle\Action\Summon;

use Battle\Unit\Race\RaceFactory;
use Battle\Unit\Unit;
use Battle\Unit\UnitInterface;
use Exception;

class SummonImpAction extends SummonAction
{
    public const NAME = 'summon Imp';

    private $name = 'Imp';
    private $level = 1;
    private $url = '/images/avas/monsters/004.png';
    private $damage = 10;
    private $attackSpeed = 1;
    private $life = 30;
    private $melee = true;
    private $raceId = 9;

    /**
     * @return UnitInterface
     * @throws Exception
     */
    public function getSummonUnit(): UnitInterface
    {
        return new Unit(
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

    /**
     * @return string
     */
    public function getNameAction(): string
    {
        return self::NAME;
    }
}
