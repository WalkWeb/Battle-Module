<?php

declare(strict_types=1);

namespace Tests\Battle\Action\Summon;

use Battle\Action\Summon\SummonSkeletonMage;
use Battle\Command\CommandFactory;
use Battle\Result\Chat\Message;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class SummonSkeletonMageTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testSummonSkeletonMage(): void
    {
        $actionUnit = UnitFactory::createByTemplate(12);
        $enemyUnit = UnitFactory::createByTemplate(1);

        $actionCommand = CommandFactory::create([$actionUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new SummonSkeletonMage($actionUnit, $enemyCommand, $actionCommand, new Message());

        self::assertEquals(SummonSkeletonMage::NAME, $action->getNameAction());

        $unit = $action->getSummonUnit();

        self::assertEquals('Skeleton Mage', $unit->getName());
        self::assertEquals(2, $unit->getLevel());
        self::assertEquals('/images/avas/monsters/008.png', $unit->getAvatar());
        self::assertEquals(13, $unit->getDamage());
        self::assertEquals(1.2, $unit->getAttackSpeed());
        self::assertEquals(42, $unit->getLife());
        self::assertEquals(42, $unit->getTotalLife());
        self::assertFalse($unit->isMelee());
        self::assertEquals(8, $unit->getRace()->getId());
    }
}
