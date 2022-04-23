<?php

declare(strict_types=1);

namespace Battle\Unit\Race;

class Race implements RaceInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $singleName;

    /**
     * @var string
     */
    private $color;

    /**
     * @var string
     */
    private $icon;

    /**
     * @var string[]
     */
    private $abilities;

    public function __construct(
        int $id,
        string $name,
        string $singleName,
        string $color,
        string $icon,
        array $abilities
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->singleName = $singleName;
        $this->color = $color;
        $this->icon = $icon;
        $this->abilities = $abilities;
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
     * @return string[]
     */
    public function getAbilities(): array
    {
        return $this->abilities;
    }
}
