<?php

declare(strict_types=1);

namespace Battle\Effect;

use Battle\Effect\Change\ChangeCollection;
use Battle\Unit\Unit;

class Effect
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
    private $description;

    /**
     * @var Unit
     */
    private $unit;

    /**
     * @var int
     */
    private $duration;

    /**
     * @var int
     */
    private $totalDuration;

    /**
     * @var ChangeCollection
     */
    private $changesApply;

    /**
     * @var ChangeCollection
     */
    private $changesDuration;

    public function __construct(
        int $id,
        string $name,
        string $description,
        Unit $unit,
        int $duration,
        ChangeCollection $changesApply,
        ChangeCollection $changesDuration
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->unit = $unit;
        $this->duration = $duration;
        $this->totalDuration = $duration;
        $this->changesApply = $changesApply;
        $this->changesDuration = $changesDuration;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUnit(): Unit
    {
        return $this->unit;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function getTotalDuration(): int
    {
        return $this->totalDuration;
    }

    public function getChangesApply(): ChangeCollection
    {
        return $this->changesApply;
    }

    public function getChangesDuration(): ChangeCollection
    {
        return $this->changesDuration;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
