<?php

declare(strict_types=1);

namespace Battle\Unit\Ability;

use Battle\Action\ActionFactory;
use Battle\Container\ContainerInterface;
use Battle\Unit\UnitInterface;
use Exception;

abstract class AbstractAbility implements AbilityInterface
{
    private array $typesActivate = [
        AbilityInterface::ACTIVATE_CONCENTRATION,
        AbilityInterface::ACTIVATE_RAGE,
        AbilityInterface::ACTIVATE_LOW_LIFE,
        AbilityInterface::ACTIVATE_DEAD,
        AbilityInterface::ACTIVATE_CUNNING,
    ];

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var string
     */
    protected string $icon;

    /**
     * @var array
     */
    protected array $actionsData;

    /**
     * @var ActionFactory
     */
    protected ActionFactory $actionFactory;

    /**
     * @var bool
     */
    protected bool $ready = false;

    /**
     * @var bool
     */
    protected bool $disposable;

    /**
     * @var bool
     */
    protected bool $usage = false;

    /**
     * @var UnitInterface
     */
    protected UnitInterface $unit;

    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var int
     */
    protected int $typeActivate;

    /**
     * Шанс активации способности. Например для способностей которые активируются при смерти
     *
     * @var int
     */
    protected int $chanceActivate;

    /**
     * Допустимые типы оружия для использования способности. Если пустой массив - значит нет требований к типу оружия
     *
     * @var int[]
     */
    protected array $allowedWeaponTypes;

    /**
     * @param UnitInterface $unit
     * @param bool $disposable
     * @param string $name
     * @param string $icon
     * @param array $actionsData
     * @param int $typeActivate
     * @param array $allowedWeaponTypes
     * @param int $chanceActivate
     * @throws Exception
     */
    public function __construct(
        UnitInterface $unit,
        bool $disposable,
        string $name,
        string $icon,
        array $actionsData,
        int $typeActivate,
        array $allowedWeaponTypes,
        int $chanceActivate = 100
    )
    {
        $this->unit = $unit;
        $this->disposable = $disposable;
        $this->chanceActivate = $chanceActivate;
        $this->container = $unit->getContainer();
        $this->name = $name;
        $this->icon = $icon;
        $this->allowedWeaponTypes = $allowedWeaponTypes;
        $this->actionFactory = $this->container->getActionFactory();
        $this->typeActivate = $this->validateTypeActivate($typeActivate);
        $this->actionsData = $this->validateActionsData($actionsData);
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
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @return bool
     */
    public function isReady(): bool
    {
        return $this->ready;
    }

    /**
     * @return UnitInterface
     */
    public function getUnit(): UnitInterface
    {
        return $this->unit;
    }

    /**
     * @return bool
     */
    public function isDisposable(): bool
    {
        return $this->disposable;
    }

    /**
     * @return bool
     */
    public function isUsage(): bool
    {
        return $this->usage;
    }

    public function getTypeActivate(): int
    {
        return $this->typeActivate;
    }

    /**
     * @return int
     */
    public function getChanceActivate(): int
    {
        return $this->chanceActivate;
    }

    /**
     * @return int[]
     */
    public function getAllowedWeaponTypes(): array
    {
        return $this->allowedWeaponTypes;
    }

    /**
     * @param int $typeActivate
     * @return int
     * @throws Exception
     */
    private function validateTypeActivate(int $typeActivate): int
    {
        if (!in_array($typeActivate, $this->typesActivate, true)) {
            throw new AbilityException(AbilityException::UNKNOWN_ACTIVATE_TYPE . ': ' . $typeActivate);
        }

        return $typeActivate;
    }

    /**
     * @param array $actionsData
     * @return array
     * @throws Exception
     */
    private function validateActionsData(array $actionsData): array
    {
        foreach ($actionsData as $actionData) {
            // Проверяем, что передан массив из массивов
            // Дальнейшая валидация будет происходить в ActionFactory
            if (!is_array($actionData)) {
                throw new AbilityException(AbilityException::INVALID_ACTION_DATA);
            }
        }

        return $actionsData;
    }
}
