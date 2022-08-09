<?php

declare(strict_types=1);

namespace Battle\Unit\Classes;

use Battle\Container\ContainerInterface;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\AbilityFactory;
use Battle\Unit\UnitInterface;
use Exception;

class UnitClass implements UnitClassInterface
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
    private string $smallIcon;

    /**
     * @var array
     */
    private array $abilitiesData;

    /**
     * @var AbilityFactory
     */
    private AbilityFactory $abilityFactory;

    /**
     * @param int $id
     * @param string $name
     * @param string $smallIcon
     * @param array $abilitiesData
     * @param ContainerInterface $container
     * @throws Exception
     */
    public function __construct(
        int $id,
        string $name,
        string $smallIcon,
        array $abilitiesData,
        ContainerInterface $container
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->smallIcon = $smallIcon;
        $this->abilitiesData = $this->validateAbilitiesData($abilitiesData);
        $this->abilityFactory = $container->getAbilityFactory();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSmallIcon(): string
    {
        return $this->smallIcon;
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

    /**
     * @param array $abilitiesData
     * @return array
     * @throws Exception
     */
    private function validateAbilitiesData(array $abilitiesData): array
    {
        foreach ($abilitiesData as $abilityData) {
            // Проверяем что передан массив из массивов
            // Дальнейшая валидация будет проходить в AbilityFactory
            if (!is_array($abilityData)) {
                throw new UnitClassException(UnitClassException::INVALID_ABILITY_DATA);
            }
        }

        return $abilitiesData;
    }
}
