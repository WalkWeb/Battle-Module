<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Damage;

use Battle\Action\DamageAction;
use Battle\Command\CommandFactory;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Damage\HellfireAbility;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class HellfireAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/276.png" alt="" /> <span class="ability">Hellfire</span> at <span style="color: #1e72e3">unit_2</span>, <span style="color: #1e72e3">unit_3</span> and <span style="color: #1e72e3">unit_4</span> on 80 damage';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/276.png" alt="" /> <span class="ability">Адское пламя</span> по <span style="color: #1e72e3">unit_2</span>, <span style="color: #1e72e3">unit_3</span> и <span style="color: #1e72e3">unit_4</span> на 80 урона';

    /**
     * @throws Exception
     */
    public function testHellfireAbilityUse(): void
    {
        $name = 'Hellfire';
        $icon = '/images/icons/ability/276.png';

        $unit = UnitFactory::createByTemplate(1);
        $firstEnemyUnit = UnitFactory::createByTemplate(2);
        $secondaryEnemyUnit = UnitFactory::createByTemplate(3);
        $thirdEnemyUnit = UnitFactory::createByTemplate(4);

        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$firstEnemyUnit, $secondaryEnemyUnit, $thirdEnemyUnit]);

        $ability = new HellfireAbility($unit);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        // Up rage
        for ($i = 0; $i < 20; $i++) {
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
            self::assertInstanceOf(DamageAction::class, $action);
            self::assertEquals((int)($unit->getDamage() * 1.5), $action->getPower());
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));
        }

        $ability->usage();

        self::assertFalse($ability->isReady());
    }
}