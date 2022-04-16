<?php

declare(strict_types=1);

namespace Battle\Unit\Ability\Summon;

use Battle\Action\ActionCollection;
use Battle\Action\SummonAction;
use Battle\Command\CommandInterface;
use Battle\Traits\IdTrait;
use Battle\Unit\Ability\AbstractAbility;
use Battle\Unit\Defense\DefenseFactory;
use Battle\Unit\Offense\OffenseFactory;
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
    private $summonAccuracy;

    /**
     * @var int
     */
    private $summonDefense;

    /**
     * @var int
     */
    private $summonBlock;

    /**
     * @var int
     */
    private $summonBlockIgnore;

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

    /**
     * @var string
     */
    private $icon;

    public function __construct(
        UnitInterface $unit,
        string $summonName,
        int $summonLevel,
        string $summonAvatar,
        int $summonDamage,
        float $summonAttackSpeed,
        int $summonAccuracy,
        int $summonDefense,
        int $summonBlock,
        int $summonBlockIgnore,
        int $summonLife,
        bool $summonMelee,
        int $summonRaceId,
        string $icon = ''
    )
    {
        parent::__construct($unit);
        $this->summonName = $summonName;
        $this->summonLevel = $summonLevel;
        $this->summonAvatar = $summonAvatar;
        $this->summonDamage = $summonDamage;
        $this->summonAttackSpeed = $summonAttackSpeed;
        $this->summonAccuracy = $summonAccuracy;
        $this->summonDefense = $summonDefense;
        $this->summonBlock = $summonBlock;
        $this->summonBlockIgnore = $summonBlockIgnore;
        $this->summonLife = $summonLife;
        $this->summonMelee = $summonMelee;
        $this->summonRaceId = $summonRaceId;
        $this->icon = $icon;
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

        // TODO Вынести создание Offense и Defense в дочерние классы

        $collection->add(new SummonAction(
            $this->unit,
            $enemyCommand,
            $alliesCommand,
            $this->getName(),
            new Unit(
                self::generateId(),
                $this->summonName,
                $this->summonLevel,
                $this->summonAvatar,
                $this->summonLife,
                $this->summonLife,
                $this->summonMelee,
                $this->unit->getCommand(),
                OffenseFactory::create([
                    'damage'       => $this->summonDamage,
                    'attack_speed' => $this->summonAttackSpeed,
                    'accuracy'     => $this->summonAccuracy,
                    'block_ignore' => $this->summonBlockIgnore,
                ]),
                DefenseFactory::create([
                    'defense' => $this->summonDefense,
                    'block'   => $this->summonBlock,
                ]),
                RaceFactory::create($this->summonRaceId),
                $this->unit->getContainer()
            ),
            $this->icon
        ));

        return $collection;
    }

    /**
     * Призыв новых существ всегда доступен - ограничений мест в команде нет
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return bool
     */
    public function canByUsed(CommandInterface $enemyCommand, CommandInterface $alliesCommand): bool
    {
        return true;
    }
}
