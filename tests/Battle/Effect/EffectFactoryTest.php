<?php

declare(strict_types=1);

namespace Tests\Battle\Effect;

use Battle\Classes\ClassFactoryException;
use Battle\Effect\Change\Change;
use Battle\Effect\Change\ChangeException;
use Battle\Effect\Effect;
use Battle\Effect\EffectException;
use Battle\Effect\EffectFactory;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class EffectFactoryTest extends TestCase
{
    /**
     * @throws ClassFactoryException
     * @throws EffectException
     * @throws UnitFactoryException
     * @throws ChangeException
     */
    public function testCreate(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $effect = EffectFactory::create(1, $unit);
        $data = EffectFactory::getAll()[1];

        self::assertContainsOnlyInstancesOf(Effect::class, [$effect]);
        self::assertEquals($data['id'], $effect->getId());
        self::assertEquals($data['name'], $effect->getName());
        self::assertEquals($data['description'], $effect->getDescription());
        self::assertEquals($data['duration'], $effect->getDuration());
        self::assertEquals($data['duration'], $effect->getTotalDuration());
        self::assertEquals($unit, $effect->getUnit());

        $changesApply = $effect->getChangesApply();
        self::assertCount(count($data['change_apply']), $changesApply->getChanges());

        foreach ($changesApply->getChanges() as $change) {
            self::assertContainsOnlyInstancesOf(Change::class, [$change]);
            self::assertEquals($data['change_apply'][0]['type'], $change->getType());
            self::assertEquals($data['change_apply'][0]['increased'], $change->isIncreased());
            self::assertEquals($data['change_apply'][0]['multiplier'], $change->isMultiplier());
            self::assertEquals($data['change_apply'][0]['power'], $change->getPower());
        }

        $changesDuration = $effect->getChangesDuration();
        self::assertCount(count($data['change_duration']), $changesDuration->getChanges());
    }
}
