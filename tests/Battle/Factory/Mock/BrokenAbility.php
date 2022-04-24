<?php

declare(strict_types=1);

namespace Tests\Battle\Factory\Mock;

use Battle\Unit\UnitInterface;

/**
 * Некорректный класс способности юнита, который не реализует интерфейс AbilityInterface
 *
 * @package Tests\Battle\Factory\Mock
 */
class BrokenAbility
{
    /**
     * @var UnitInterface
     */
    private $unit;

    public function __construct(UnitInterface $unit)
    {
        $this->unit = $unit;
    }

    /**
     * @return UnitInterface
     */
    public function getUnit(): UnitInterface
    {
        return $this->unit;
    }
}
