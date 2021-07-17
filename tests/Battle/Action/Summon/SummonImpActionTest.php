<?php

declare(strict_types=1);

namespace Tests\Battle\Action\Summon;

use Exception;
use Battle\Action\Summon\SummonImpAction;
use Battle\Command\CommandFactory;
use Battle\Result\Chat\Message;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class SummonImpActionTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testSummonImpActionCreate(): void
    {
        $actionUnit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(1);

        $actionCommand = CommandFactory::create([$actionUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new SummonImpAction($actionUnit, $enemyCommand, $actionCommand, new Message());

        self::assertTrue($action->canByUsed());

        self::assertEquals(SummonImpAction::NAME, $action->getNameAction());

        $unit = $action->getSummonUnit();

        self::assertEquals('Imp', $unit->getName());
        self::assertEquals(1, $unit->getLevel());
        self::assertEquals('/images/avas/monsters/004.png', $unit->getAvatar());
        self::assertEquals(10, $unit->getDamage());
        self::assertEquals(1, $unit->getAttackSpeed());
        self::assertEquals(30, $unit->getLife());
        self::assertEquals(30, $unit->getTotalLife());
        self::assertTrue($unit->isMelee());
        self::assertEquals(9, $unit->getRace()->getId());
    }
}
