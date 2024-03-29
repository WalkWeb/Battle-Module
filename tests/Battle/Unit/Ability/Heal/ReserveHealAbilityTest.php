<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Heal;

use Battle\Action\HealAction;
use Battle\Command\CommandFactory;
use Battle\Response\Scenario\Scenario;
use Battle\Response\Statistic\Statistic;
use Battle\Unit\Ability\AbilityInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class ReserveHealAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_4</span> use <img src="/images/icons/ability/432.png" alt="" /> <span class="ability">Reserve Heal</span> and heal <span style="color: #1e72e3">wounded_unit</span> on %d life';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_4</span> использовал <img src="/images/icons/ability/432.png" alt="" /> <span class="ability">Резервное Лечение</span> и вылечил <span style="color: #1e72e3">wounded_unit</span> на %d здоровья';

    /**
     * Тест на создание способности Reserve Heal через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testReserveHealAbilityCreate(): void
    {
        $name = 'Reserve Heal';
        $icon = '/images/icons/ability/432.png';
        $disposable = false;

        $unit = UnitFactory::createByTemplate(4);
        $alliesUnit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit, $alliesUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->getAbility($unit, $name, 1);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
        self::assertEquals($disposable, $ability->isDisposable());
        self::assertFalse($ability->isUsage());
        self::assertEquals(AbilityInterface::ACTIVATE_CUNNING, $ability->getTypeActivate());
        self::assertEquals([], $ability->getAllowedWeaponTypes());

        $actions = $ability->getActions($enemyCommand, $command);

        self::assertCount(1, $actions);

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertInstanceOf(HealAction::class, $action);
            self::assertEquals($name, $action->getNameAction());
            self::assertEquals($icon, $action->getIcon());
        }
    }

    /**
     * Тест на применение способности Reserve Heal
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param int $expectedPower
     * @throws Exception
     */
    public function testReserveHealAbilityUse(int $level, int $expectedPower): void
    {
        $unit = UnitFactory::createByTemplate(4);
        $alliesUnit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit, $alliesUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->getAbility($unit, 'Reserve Heal', $level);

        $this->activateAbility($ability, $unit);

        self::assertTrue($ability->isReady());

        $actions = $ability->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals($expectedPower, $action->getFactualPower());
            self::assertEquals(sprintf(self::MESSAGE_EN, $expectedPower), $this->getChat()->addMessage($action));
            self::assertEquals(sprintf(self::MESSAGE_RU, $expectedPower), $this->getChatRu()->addMessage($action));

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
                24,
            ],
            [
                2,
                37,
            ],
            [
                3,
                62,
            ],
            [
                4,
                98,
            ],
            [
                5,
                141,
            ],
        ];
    }
}
