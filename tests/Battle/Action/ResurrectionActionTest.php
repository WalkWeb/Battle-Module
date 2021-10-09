<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\ActionException;
use Battle\Action\ResurrectionAction;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\BaseFactory;

class ResurrectionActionTest extends TestCase
{
    /**
     * Тест на создание ResurrectionAction
     *
     * @throws Exception
     */
    public function testResurrectionActionCreate(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(10, 2);

        $name = 'Resurrection';
        $power = 50;
        $typeTarget = ResurrectionAction::TARGET_DEAD_ALLIES;

        $action = new ResurrectionAction($unit, $enemyCommand, $command, $typeTarget, $power, $name);

        self::assertEquals($name, $action->getNameAction());
        self::assertEquals($power, $action->getPower());
        self::assertEquals($typeTarget, $action->getTypeTarget());
        self::assertEquals('applyResurrectionAction', $action->getHandleMethod());
        self::assertEquals(ResurrectionAction::EFFECT_ANIMATION_METHOD, $action->getAnimationMethod());
        self::assertEquals('resurrected', $action->getMessageMethod());
        self::assertTrue($action->canByUsed());

        $factualPower = 123;

        $action->setFactualPower($factualPower);

        self::assertEquals($factualPower, $action->getFactualPower());
    }

    /**
     * Тест на применение ResurrectionAction
     *
     * @throws Exception
     */
    public function testResurrectionActionApply(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(10, 2);

        $name = 'Resurrection';
        $power = 50;
        $typeTarget = ResurrectionAction::TARGET_DEAD_ALLIES;

        $action = new ResurrectionAction($unit, $enemyCommand, $command, $typeTarget, $power, $name);

        self::assertFalse($unit->isAlive());
        self::assertEquals(0, $unit->getLife());

        self::assertTrue($action->canByUsed());
        $action->handle();

        self::assertTrue($unit->isAlive());
        self::assertEquals(50, $unit->getLife());
    }

    /**
     * Тест на различные варианты некорректной силы ResurrectionAction - меньше 1 или больше 100
     *
     * @dataProvider invalidPowerDataProvider
     * @param int $power
     * @throws ActionException
     * @throws Exception
     */
    public function testResurrectionActionInvalidPower(int $power): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(10, 2);

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage('ResurrectionAction: invalid power: min: 1, max: 100');
        new ResurrectionAction($unit, $enemyCommand, $command, ResurrectionAction::TARGET_DEAD_ALLIES, $power);
    }

    /**
     * @return array
     */
    public function invalidPowerDataProvider(): array
    {
        return [
            [
                -10
            ],
            [
                0
            ],
            [
                101
            ],
            [
                1000
            ],
        ];
    }
}
