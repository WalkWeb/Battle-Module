<?php

declare(strict_types=1);

namespace Tests\Battle\Effect;

use Battle\Classes\ClassFactoryException;
use Battle\Effect\Change\Change;
use Battle\Effect\Change\ChangeCollection;
use Battle\Effect\Effect;
use Battle\Effect\EffectFactory;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class EffectTest extends TestCase
{
    /**
     * @throws ClassFactoryException
     * @throws UnitFactoryException
     */
    public function testCreate(): void
    {
        $data = EffectFactory::getAll()[2];

        $unit = UnitFactory::create(1);

        $change = new Change(
            $data['change_apply'][0]['type'],
            $data['change_apply'][0]['increased'],
            $data['change_apply'][0]['multiplier'],
            $data['change_apply'][0]['power']
        );

        $changesApply = new ChangeCollection();

        $changesApply->add($change);

        $changesDuration = new ChangeCollection();

        $effect = new Effect(
            $data['id'],
            $data['name'],
            $data['description'],
            $unit,
            $data['duration'],
            $changesApply,
            $changesDuration
        );

        $this->assertEquals($data['id'], $effect->getId());
        $this->assertEquals($data['name'], $effect->getName());
        $this->assertEquals($data['description'], $effect->getDescription());
        $this->assertEquals($data['duration'], $effect->getDuration());
        $this->assertEquals($data['duration'], $effect->getTotalDuration());
        $this->assertEquals($unit, $effect->getUnit());

        $changesApply = $effect->getChangesApply();
        $this->assertCount(count($data['change_apply']), $changesApply->getChanges());

        foreach ($changesApply->getChanges() as $change) {
            $this->assertContainsOnlyInstancesOf(Change::class, [$change]);
            $this->assertEquals($data['change_apply'][0]['type'], $change->getType());
            $this->assertEquals($data['change_apply'][0]['increased'], $change->isIncreased());
            $this->assertEquals($data['change_apply'][0]['multiplier'], $change->isMultiplier());
            $this->assertEquals($data['change_apply'][0]['power'], $change->getPower());
        }

        $changesDuration = $effect->getChangesDuration();
        $this->assertCount(count($data['change_duration']), $changesDuration->getChanges());
    }
}
