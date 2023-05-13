<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Damage;

use Battle\Action\DamageAction;
use Battle\Command\CommandFactory;
use Battle\Response\Scenario\Scenario;
use Battle\Response\Statistic\Statistic;
use Battle\Weapon\Type\WeaponTypeInterface;
use Exception;
use Tests\Battle\Unit\Ability\AbstractAbilityTest;
use Tests\Factory\UnitFactory;

class BiteAbilityTest extends AbstractAbilityTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/458.png" alt="" /> <span class="ability">Bite</span> and hit for %d damage against <span style="color: #1e72e3">unit_2</span> and restore %d life';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/458.png" alt="" /> <span class="ability">Укус</span> и нанес удар на %d урона по <span style="color: #1e72e3">unit_2</span> и восстановил %d здоровья';

    /**
     * Тест на создание способности Bite через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testBiteAbilityCreate(): void
    {
        $name = 'Bite';
        $icon = '/images/icons/ability/458.png';
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
        self::assertEquals(
            [
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
                WeaponTypeInterface::DAGGER,
            ],
            $ability->getAllowedWeaponTypes()
        );

        $actions = $ability->getActions($enemyCommand, $command);

        self::assertCount(1, $actions);

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertEquals($name, $action->getNameAction());
            self::assertEquals($icon, $action->getIcon());
            // Проверка конвертации физического урона в урон магией смерти
            self::assertTrue($action->getOffense()->getDeathDamage() > 0);
        }
    }

    /**
     * Тест на применение способности Bite
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param int $expectedDamage
     * @param int $expectedAccuracy
     * @param int $expectedCriticalChance
     * @param int $expectedLifeSteal
     * @throws Exception
     */
    public function testBiteAbilityUse(
        int $level,
        int $expectedDamage,
        int $expectedAccuracy,
        int $expectedCriticalChance,
        int $expectedLifeSteal
    ): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, 'Bite', $level);

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
            self::assertEquals(sprintf(self::MESSAGE_EN, $expectedDamage, $expectedLifeSteal), $this->getChat()->addMessage($action));
            self::assertEquals(sprintf(self::MESSAGE_RU, $expectedDamage, $expectedLifeSteal), $this->getChatRu()->addMessage($action));

            // Проверка вампиризма
            self::assertEquals(50, $action->getOffense()->getVampirism());

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
                16,
                300,
                10,
                8,
            ],
            [
                2,
                17,
                320,
                10,
                8,
            ],
            [
                3,
                18,
                340,
                10,
                9,
            ],
            [
                4,
                19,
                360,
                10,
                9,
            ],
            [
                5,
                20,
                380,
                10,
                10,
            ],
        ];
    }
}
