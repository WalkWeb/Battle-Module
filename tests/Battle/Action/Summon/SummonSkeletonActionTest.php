<?php

declare(strict_types=1);

namespace Tests\Battle\Action\Summon;

use Exception;
use Battle\Result\Chat\Message;
use PHPUnit\Framework\TestCase;
use Battle\Command\CommandFactory;
use Tests\Battle\Factory\UnitFactory;
use Battle\Action\Summon\SummonSkeletonAction;

class SummonSkeletonActionTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testSummonSkeletonActionCreate(): void
    {
        $actionUnit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(1);

        $actionCommand = CommandFactory::create([$actionUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new SummonSkeletonAction($actionUnit, $enemyCommand, $actionCommand, new Message());

        self::assertEquals(SummonSkeletonAction::NAME, $action->getNameAction());

        $unit = $action->getSummonUnit();

        self::assertEquals('Skeleton', $unit->getName());
        self::assertEquals(1, $unit->getLevel());
        self::assertEquals('/images/avas/monsters/003.png', $unit->getAvatar());
        self::assertEquals(16, $unit->getDamage());
        self::assertEquals(1, $unit->getAttackSpeed());
        self::assertEquals(38, $unit->getLife());
        self::assertEquals(38, $unit->getTotalLife());
        self::assertTrue($unit->isMelee());
        self::assertEquals(8, $unit->getRace()->getId());
    }
}
