<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Effect;

use Battle\Action\ActionCollection;
use Battle\Action\BuffAction;
use Battle\Action\EffectAction;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Effect\ReserveForcesAbility;
use Battle\Unit\Effect\Effect;
use Battle\Unit\Effect\EffectCollection;
use Battle\Unit\UnitInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class ReserveForcesAbilityTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testReserveForcesAbility(): void
    {
        $name = 'Reserve Forces';
        $icon = '/images/icons/ability/156.png';
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new ReserveForcesAbility($unit);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $collection = new AbilityCollection();
        $collection->add($ability);

        foreach ($collection as $item) {
            self::assertEquals($ability, $item);
        }

        $collection->update($unit);

        self::assertTrue($ability->isReady());

        self::assertEquals(
            $this->getReserveForcesActions($unit, $enemyCommand, $command),
            $ability->getAction($enemyCommand, $command)
        );

        $ability->usage();

        self::assertFalse($ability->isReady());

        // TODO Дописать тест на применение пока нельзя - т.к. нет класса, который бы использовал данную способность
    }

    private function getReserveForcesActions(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand
    ): ActionCollection
    {
        $name = 'Reserve Forces';
        $icon = '/images/icons/ability/156.png';
        $useMessage = 'use Reserve Forces for self';
        $duration = 6;
        $modifyMethod = 'multiplierMaxLife';
        $modifyPower = 130;

        $collection = new ActionCollection();

        // Создаем коллекцию событий (с одним бафом), которая будет применена к персонажу, при применении эффекта
        $onApplyActionCollection = new ActionCollection();

        $onApplyActionCollection->add(new BuffAction(
            $unit,
            $enemyCommand,
            $alliesCommand,
            $useMessage,
            $modifyMethod,
            $modifyPower
        ));

        // Создаем коллекцию эффектов, с одним эффектом при применении - Reserve Forces
        $effects = new EffectCollection();

        $effects->add(new Effect(
            $name,
            $icon,
            $duration,
            $onApplyActionCollection,
            new ActionCollection(),
            new ActionCollection()
        ));

        // Создаем сам эффект
        $collection->add(new EffectAction(
            $unit,
            $enemyCommand,
            $alliesCommand,
            $name,
            $effects
        ));

        return $collection;
    }
}
