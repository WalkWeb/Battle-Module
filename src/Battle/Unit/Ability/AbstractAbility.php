<?php

declare(strict_types=1);

namespace Battle\Unit\Ability;

use Battle\Container\ContainerInterface;
use Battle\Unit\UnitInterface;

abstract class AbstractAbility implements AbilityInterface
{
    private $typesActivate = [
        AbilityInterface::ACTIVATE_CONCENTRATION,
        AbilityInterface::ACTIVATE_RAGE,
        AbilityInterface::ACTIVATE_LOW_LIFE,
        AbilityInterface::ACTIVATE_DEAD,
    ];

    /**
     * @var bool
     */
    protected $ready = false;

    /**
     * @var bool
     */
    protected $disposable;

    /**
     * @var bool
     */
    protected $usage = false;

    /**
     * @var UnitInterface
     */
    protected $unit;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var int
     */
    protected $typeActivate;

    /**
     * @var int
     */
    protected $chanceActivate;

    /**
     * TODO Дефолтный вариант для $typeActivate задается временно - когда конкретные классы способностей будут удалены
     * TODO дефолтный вариант также нужно будет удалить
     *
     * @param UnitInterface $unit
     * @param bool $disposable
     * @param int $chanceActivate
     * @param int $typeActivate
     * @throws AbilityException
     */
    public function __construct(UnitInterface $unit, bool $disposable, int $chanceActivate = 100, int $typeActivate = 1)
    {
        $this->unit = $unit;
        $this->disposable = $disposable;
        $this->chanceActivate = $chanceActivate;
        $this->typeActivate = $this->validateTypeActivate($typeActivate);
        $this->container = $unit->getContainer();
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
     * @param int $typeActivate
     * @return int
     * @throws AbilityException
     */
    private function validateTypeActivate(int $typeActivate): int
    {
        if (!in_array($typeActivate, $this->typesActivate, true)) {
            throw new AbilityException(AbilityException::UNKNOWN_ACTIVATE_TYPE . ': ' . $typeActivate);
        }

        return $typeActivate;
    }
}
