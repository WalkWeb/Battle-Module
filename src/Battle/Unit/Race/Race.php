<?php

declare(strict_types=1);

namespace Battle\Unit\Race;

use Battle\Container\ContainerInterface;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\AbilityFactory;
use Battle\Unit\UnitInterface;
use Exception;

class Race implements RaceInterface
{
    /**
     * @var int
     */
    private int $id;

    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $singleName;

    /**
     * @var string
     */
    private string $color;

    /**
     * @var string
     */
    private string $icon;

    /**
     * @var array
     */
    private array $abilitiesData;

    /**
     * @var AbilityFactory
     */
    private AbilityFactory $abilityFactory;

    public function __construct(
        int $id,
        string $name,
        string $singleName,
        string $color,
        string $icon,
        array $abilitiesData,
        ContainerInterface $container
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->singleName = $singleName;
        $this->color = $color;
        $this->icon = $icon;
        $this->abilitiesData = $abilitiesData;
        $this->abilityFactory = $container->getAbilityFactory();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSingleName(): string
    {
        return $this->singleName;
    }

    /**
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @param UnitInterface $unit
     * @return AbilityCollection
     * @throws Exception
     */
    public function getAbilities(UnitInterface $unit): AbilityCollection
    {
        $collection = new AbilityCollection();

        foreach ($this->abilitiesData as $abilityData) {
            $collection->add(
                $this->abilityFactory->create($unit, $abilityData)
            );
        }

        return $collection;
    }
}
