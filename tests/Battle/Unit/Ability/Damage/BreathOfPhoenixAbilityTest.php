<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Damage;

use Battle\Action\DamageAction;
use Battle\Command\CommandFactory;
use Battle\Response\Scenario\Scenario;
use Battle\Response\Statistic\Statistic;
use Exception;
use Tests\Battle\Unit\Ability\AbstractAbilityTest;
use Tests\Factory\UnitFactory;

class BreathOfPhoenixAbilityTest extends AbstractAbilityTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/419.png" alt="" /> <span class="ability">Breath of Phoenix</span> and hit for %d damage against <span style="color: #1e72e3">unit_2</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/419.png" alt="" /> <span class="ability">Дыхание феникса</span> и нанес удар на %d урона по <span style="color: #1e72e3">unit_2</span>';

    /**
     * Тест на создание способности Breath of Phoenix через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testBreathOfPhoenixAbilityCreate(): void
    {
        $name = 'Breath of Phoenix';
        $icon = '/images/icons/ability/419.png';
        $disposable = false;

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, $name, 1);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
        self::assertEquals($disposable, $ability->isDisposable());
        self::assertFalse($ability->isUsage());
        self::assertEquals([], $ability->getAllowedWeaponTypes());

        $actions = $ability->getActions($enemyCommand, $command);

        self::assertCount(1, $actions);

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertEquals($name, $action->getNameAction());
            self::assertEquals($icon, $action->getIcon());
        }
    }

    /**
     * Тест на применение способности Breath of Phoenix
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param int $expectedDamage
     * @param int $expectedAccuracy
     * @param int $expectedCriticalChance
     * @throws Exception
     */
    public function testBreathOfPhoenixAbilityUse(int $level, int $expectedDamage, int $expectedAccuracy, int $expectedCriticalChance): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, 'Breath of Phoenix', $level);

        $this->activateAbility($ability, $unit);

        self::assertTrue($ability->isReady());

        $actions = $ability->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertInstanceOf(DamageAction::class, $action);
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals($expectedDamage, $action->getFactualPower());
            self::assertEquals($expectedAccuracy, $action->getOffense()->getAccuracy());
            self::assertEquals($expectedCriticalChance, $action->getOffense()->getCriticalChance());
            self::assertEquals(sprintf(self::MESSAGE_EN, $expectedDamage), $this->getChat()->addMessage($action));
            self::assertEquals(sprintf(self::MESSAGE_RU, $expectedDamage), $this->getChatRu()->addMessage($action));

            // Дополнительное проверяем, что по событию успешно создается анимация
            (new Scenario())->addAnimation($action, new Statistic());
        }

        $ability->usage();
        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
    }

    /**
     * @return array
     */
    public function useDataProvider(): array
    {
        return [
            [
                1,
                26,
                280,
                10,
            ],
            [
                2,
                27,
                300,
                11,
            ],
            [
                3,
                28,
                320,
                12,
            ],
            [
                4,
                29,
                340,
                13,
            ],
            [
                5,
                30,
                360,
                14,
            ],
        ];
    }
}
