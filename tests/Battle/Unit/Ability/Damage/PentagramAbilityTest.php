<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Damage;

use Battle\Action\DamageAction;
use Battle\Command\CommandFactory;
use Battle\Response\Scenario\Scenario;
use Battle\Response\Statistic\Statistic;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Weapon\Type\WeaponTypeInterface;
use Exception;
use Tests\Battle\Unit\Ability\AbstractAbilityTest;
use Tests\Factory\UnitFactory;

class PentagramAbilityTest extends AbstractAbilityTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/430.png" alt="" /> <span class="ability">Pentagram</span> and hit for %d damage against <span style="color: #1e72e3">unit_2</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/430.png" alt="" /> <span class="ability">Пентаграмма</span> и нанес удар на %d урона по <span style="color: #1e72e3">unit_2</span>';

    /**
     * Тест на создание способности Pentagram через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testPentagramAbilityCreate(): void
    {
        $name = 'Pentagram';
        $icon = '/images/icons/ability/430.png';
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
        self::assertEquals(AbilityInterface::ACTIVATE_CONCENTRATION, $ability->getTypeActivate());
        self::assertEquals([
            WeaponTypeInterface::SWORD,
            WeaponTypeInterface::AXE,
            WeaponTypeInterface::MACE,
            WeaponTypeInterface::TWO_HAND_SWORD,
            WeaponTypeInterface::TWO_HAND_AXE,
            WeaponTypeInterface::TWO_HAND_MACE,
            WeaponTypeInterface::HEAVY_TWO_HAND_SWORD,
            WeaponTypeInterface::HEAVY_TWO_HAND_AXE,
            WeaponTypeInterface::HEAVY_TWO_HAND_MACE,
            WeaponTypeInterface::SPEAR,
            WeaponTypeInterface::LANCE,
        ],
            $ability->getAllowedWeaponTypes()
        );

        $actions = $ability->getActions($enemyCommand, $command);

        self::assertCount(1, $actions);

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertEquals($name, $action->getNameAction());
            self::assertEquals($icon, $action->getIcon());
        }
    }

    /**
     * Тест на применение способности Pentagram
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param int $expectedDamage
     * @param int $expectedAccuracy
     * @param int $expectedCriticalChance
     * @throws Exception
     */
    public function testPentagramAbilityUse(int $level, int $expectedDamage, int $expectedAccuracy, int $expectedCriticalChance): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, 'Pentagram', $level);

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
                22,
                600,
                10,
            ],
            [
                2,
                23,
                640,
                11,
            ],
            [
                3,
                24,
                680,
                12,
            ],
            [
                4,
                25,
                720,
                13,
            ],
            [
                5,
                26,
                760,
                14,
            ],
        ];
    }
}
