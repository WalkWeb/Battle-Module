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
    }
}
