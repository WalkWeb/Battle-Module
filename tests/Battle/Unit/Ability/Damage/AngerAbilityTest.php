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

class AngerAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/523.png" alt="" /> <span class="ability">Anger</span> and hit for %d damage against <span style="color: #1e72e3">unit_2</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/523.png" alt="" /> <span class="ability">Гнев</span> и нанес удар на %d урона по <span style="color: #1e72e3">unit_2</span>';

    /**
     * Тест на создание способности Anger через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testAngerAbilityCreate(): void
    {
        $name = 'Anger';
        $icon = '/images/icons/ability/523.png';
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
                WeaponTypeInterface::DAGGER,
            ],
            $ability->getAllowedWeaponTypes()
        );

        $actions = $ability->getActions($enemyCommand, $command);

        self::assertCount(1, $actions);

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertEquals($name, $action->getNameAction());
            self::assertEquals($icon, $action->getIcon());
            // Проверка конвертации физического урона в урон огнем
            self::assertTrue($action->getOffense()->getFireDamage() > 0);
        }
    }

    /**
     * Тест на применение способности Anger
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param int $expectedDamage
     * @param int $expectedAccuracy
     * @throws Exception
     */
    public function testAngerAbilityUse(int $level, int $expectedDamage, int $expectedAccuracy): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->getAbility($unit, 'Anger', $level);

        $this->activateAbility($ability, $unit);

        self::assertTrue($ability->isReady());

        $actions = $ability->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertInstanceOf(DamageAction::class, $action);
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals($expectedDamage, $action->getFactualPower());
            self::assertEquals($expectedAccuracy, $action->getOffense()->getAccuracy());
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
            ],
            [
                2,
                28,
                300,
            ],
            [
                3,
                30,
                320,
            ],
            [
                4,
                32,
                340,
            ],
            [
                5,
                34,
                360,
            ],
        ];
    }
}
