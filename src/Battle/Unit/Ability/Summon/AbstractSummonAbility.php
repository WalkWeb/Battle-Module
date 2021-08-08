<?php

declare(strict_types=1);

namespace Battle\Unit\Ability\Summon;

use Battle\Action\ActionCollection;
use Battle\Action\SummonAction;
use Battle\Command\CommandInterface;
use Battle\Traits\IdTrait;
use Battle\Unit\Ability\AbstractAbility;
use Battle\Unit\Race\RaceFactory;
use Battle\Unit\Unit;
use Battle\Unit\UnitInterface;
use Exception;

abstract class AbstractSummonAbility extends AbstractAbility
{
    use IdTrait;

    /**
     * @var string
     */
    private $summonName;

    /**
     * @var int
     */
    private $summonLevel;

    /**
     * @var string
     */
    private $summonAvatar;

    /**
     * @var int
     */
    private $summonDamage;

    /**
     * @var float
     */
    private $summonAttackSpeed;

    /**
     * @var int
     */
    private $summonLife;

    /**
     * @var bool
     */
    private $summonMelee;

    /**
     * @var int
     */
    private $summonRaceId;

    public function __construct(
        UnitInterface $unit,
        string $summonName,
        int $summonLevel,
        string $summonAvatar,
        int $summonDamage,
        float $summonAttackSpeed,
        int $summonLife,
        bool $summonMelee,
        int $summonRaceId)
    {
        parent::__construct($unit);
        $this->summonName = $summonName;
        $this->summonLevel = $summonLevel;
        $this->summonAvatar = $summonAvatar;
        $this->summonDamage = $summonDamage;
        $this->summonAttackSpeed = $summonAttackSpeed;
        $this->summonLife = $summonLife;
        $this->summonMelee = $summonMelee;
        $this->summonRaceId = $summonRaceId;
    }

    /**
     * Формирует коллекцию событий с призывом существа
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws Exception
     */
    public function getAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        $collection = new ActionCollection();

        $collection->add(new SummonAction(
            $this->unit,
            $enemyCommand,
            $alliesCommand,
            $this->getUseMessage(),
            new Unit(
                self::generateId(),
                $this->summonName,
                $this->summonLevel,
                $this->summonAvatar,
                $this->summonDamage,
                $this->summonAttackSpeed,
                $this->summonLife,
                $this->summonLife,
                $this->summonMelee,
                $this->unit->getCommand(),
                RaceFactory::create($this->summonRaceId),
                $this->unit->getContainer()
            )
        ));

        return $collection;
    }

    abstract public function getUseMessage(): string;
}
