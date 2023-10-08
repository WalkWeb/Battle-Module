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
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class PunishingLightAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/051.png" alt="" /> <span class="ability">Punishing Light</span> and hit for %d damage against <span style="color: #1e72e3">unit_2</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/051.png" alt="" /> <span class="ability">Карающий свет</span> и нанес удар на %d урона по <span style="color: #1e72e3">unit_2</span>';

    /**
     * Тест на создание способности Punishing Light через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testPunishingLightAbilityCreate(): void
    {
        $name = 'Punishing Light';
        $icon = '/images/icons/ability/051.png';
        $disposable = false;

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->getAbility($unit, $name, 1);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
        self::assertEquals($disposable, $ability->isDisposable());
        self::assertFalse($ability->isUsage());
        self::assertEquals(AbilityInterface::ACTIVATE_CONCENTRATION, $ability->getTypeActivate());
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
            ],
            $ability->getAllowedWeaponTypes()
        );

        $actions = $ability->getActions($enemyCommand, $command);

        self::assertCount(1, $actions);

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertEquals($name, $action->getNameAction());
            self::assertEquals($icon, $action->getIcon());
            // Проверка конвертации физического урона в урон землей
            self::assertTrue($action->getOffense()->getLifeDamage() > 0);
        }
    }

    /**
     * Тест на применение способности Punishing Light
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param int $expectedDamage
     * @param int $expectedAccuracy
     * @param int $expectedCriticalChance
     * @param int $expectedCriticalMultiplier
     * @throws Exception
     */
    public function testPunishingLightAbilityUse(
        int $level,
        int $expectedDamage,
        int $expectedAccuracy,
        int $expectedCriticalChance,
        int $expectedCriticalMultiplier
    ): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->getAbility($unit, 'Punishing Light', $level);

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
            self::assertEquals($expectedCriticalMultiplier, $action->getOffense()->getCriticalMultiplier());
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
                24,
                280,
                10,
                300,
            ],
            [
                2,
                25,
                300,
                10,
                340,
            ],
            [
                3,
                26,
                320,
                10,
                380,
            ],
            [
                4,
                27,
                340,
                10,
                420,
            ],
            [
                5,
                28,
                360,
                10,
                459, // Не 460 из-за особенностей работы с float. BCMath не используется для лучшей производительности
            ],
        ];
    }
}
