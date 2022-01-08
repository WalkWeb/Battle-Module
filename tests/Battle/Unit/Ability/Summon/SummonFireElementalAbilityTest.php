<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Summon;

use Battle\Action\SummonAction;
use Battle\Command\CommandFactory;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Summon\SummonFireElementalAbility;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class SummonFireElementalAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_1</span> summon <img src="/images/icons/ability/198.png" alt="" /> <span class="ability">Fire Elemental</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_1</span> призвал <img src="/images/icons/ability/198.png" alt="" /> <span class="ability">Элементаля огня</span>';

    /**
     * @throws Exception
     */
    public function testSummonFireElementalAbilityUse(): void
    {
        $name = 'Fire Elemental';
        $icon = '/images/icons/ability/198.png';
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new SummonFireElementalAbility($unit);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        // Up concentration
        for ($i = 0; $i < 20; $i++) {
            $unit->newRound();
        }

        $collection = new AbilityCollection();
        $collection->add($ability);
        $collection->update($unit);

        self::assertTrue($ability->isReady());

        $actions = $ability->getAction($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertInstanceOf(SummonAction::class, $action);
            self::assertTrue($action->canByUsed());
            self::assertEquals(self::MESSAGE_EN, $action->handle());
        }

        $ability->usage();

        self::assertFalse($ability->isReady());

        self::assertEquals(0, $unit->getRage());
    }

    /**
     * Тест на формирование сообщения на русском
     *
     * @throws Exception
     */
    public function testSummonFireElementalAbilityRuMessage(): void
    {
        $container = $this->getContainerWithRuLanguage();

        $unit = UnitFactory::createByTemplate(1, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new SummonFireElementalAbility($unit);

        // Up concentration
        for ($i = 0; $i < 20; $i++) {
            $unit->newRound();
        }

        $collection = new AbilityCollection();
        $collection->add($ability);
        $collection->update($unit);

        $actions = $ability->getAction($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertEquals(self::MESSAGE_RU, $action->handle());
        }
    }
}
