<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Summon;

use Exception;
use Battle\Action\SummonAction;
use Battle\Command\CommandFactory;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Summon\SummonSkeletonMageAbility;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class SummonSkeletonMageAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_1</span> summon <img src="/images/icons/ability/503.png" alt="" /> <span class="ability">Skeleton Mage</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_1</span> призвал <img src="/images/icons/ability/503.png" alt="" /> <span class="ability">Скелета-мага</span>';

    /**
     * @throws Exception
     */
    public function testSummonSkeletonMageAbilityUse(): void
    {
        $name = 'Skeleton Mage';
        $icon = '/images/icons/ability/503.png';
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new SummonSkeletonMageAbility($unit);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
        self::assertFalse($ability->isDisposable());
        self::assertFalse($ability->isUsage());

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

        $actions = $ability->getAction($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertInstanceOf(SummonAction::class, $action);
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
        }

        $ability->usage();

        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
        self::assertEquals(0, $unit->getConcentration());
    }

    /**
     * Тест на формирование сообщения на русском
     *
     * @throws Exception
     */
    public function testSummonSkeletonMageAbilityRuMessage(): void
    {
        $container = $this->getContainerWithRuLanguage();

        $unit = UnitFactory::createByTemplate(1, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new SummonSkeletonMageAbility($unit);

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $collection = new AbilityCollection();
        $collection->add($ability);
        $collection->update($unit);

        $actions = $ability->getAction($enemyCommand, $command);

        foreach ($actions as $action) {
            $action->handle();
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));
        }
    }
}
