<?php

declare(strict_types=1);

namespace Battle\Effect\Change;

class Change
{
    /**
     * @var int
     */
    private $type;

    /**
     * @var bool
     */
    private $increased;

    /**
     * @var bool
     */
    private $multiplier;

    /**
     * @var int
     */
    private $power;

    public function __construct(int $type, bool $increased, bool $multiplier, int $power)
    {
        $this->type = $type;
        $this->increased = $increased;
        $this->multiplier = $multiplier;
        $this->power = $power;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function isIncreased(): bool
    {
        return $this->increased;
    }

    public function isMultiplier(): bool
    {
        return $this->multiplier;
    }

    public function getPower(): int
    {
        return $this->power;
    }
}
