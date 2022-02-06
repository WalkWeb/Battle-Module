<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Effect;

use Battle\Command\CommandFactory;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Effect\IncinerationAbility;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class IncinerationAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/232.png" alt="" /> <span class="ability">Incineration</span> on <span style="color: #1e72e3">unit_2</span> and <span style="color: #1e72e3">unit_3</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/232.png" alt="" /> <span class="ability">Испепеление</span> на <span style="color: #1e72e3">unit_2</span> и <span style="color: #1e72e3">unit_3</span>';

    /**
     * @throws Exception
     */
    public function testIncinerationAbility(): void
    {
        $name = 'Incineration';
        $icon = '/images/icons/ability/232.png';
        $unit = UnitFactory::createByTemplate(1);
        $firstEnemyUnit = UnitFactory::createByTemplate(2);
        $secondaryEnemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$firstEnemyUnit, $secondaryEnemyUnit]);

        $ability = new IncinerationAbility($unit);

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
        $collection->update($unit);

        self::assertTrue($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        // Проверяем, что перед использованием способности вражеские юниты не имеют эффекта
        foreach ($ability->getAction($enemyCommand, $command) as $action) {
            foreach ($enemyCommand->getUnits() as $unit) {
                self::assertFalse($unit->getEffects()->exist($action->getEffect()));
            }
            self::assertTrue($action->canByUsed());
            $action->handle();

            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));
        }

        $ability->usage();

        self::assertFalse($ability->isReady());
        self::assertFalse($ability->canByUsed($enemyCommand, $command));

        // Проверяем, что после использования способности вражеские юниты имеют эффект
        foreach ($ability->getAction($enemyCommand, $command) as $action) {
            foreach ($enemyCommand->getUnits() as $unit) {
                self::assertTrue($unit->getEffects()->exist($action->getEffect()));
            }
            self::assertFalse($action->canByUsed());
        }
    }
}
