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
     * @param int $id
     * @param string $name
     * @param string $singleName
     * @param string $color
     * @param string $icon
     */
    public function __construct(int $id, string $name, string $singleName, string $color, string $icon)
    {
        $this->id = $id;
        $this->name = $name;
        $this->singleName = $singleName;
        $this->color = $color;
        $this->icon = $icon;
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
}
