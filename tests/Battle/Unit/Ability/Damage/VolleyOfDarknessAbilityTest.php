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

class VolleyOfDarknessAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">Unit</span> use <img src="/images/icons/ability/360.png" alt="" /> <span class="ability">Volley of Darkness</span> and hit for %d damage against <span style="color: #1e72e3">unit_2</span> and restore %d life';
    private const MESSAGE_RU = '<span style="color: #1e72e3">Unit</span> использовал <img src="/images/icons/ability/360.png" alt="" /> <span class="ability">Залп тьмы</span> и нанес удар на %d урона по <span style="color: #1e72e3">unit_2</span> и восстановил %d здоровья';

    /**
     * Тест на создание способности Volley of Darkness через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testVolleyOfDarknessAbilityCreate(): void
    {
        $name = 'Volley of Darkness';
        $icon = '/images/icons/ability/360.png';
        $disposable = false;

        $unit = UnitFactory::createByTemplate(45);
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
        self::assertEquals(AbilityInterface::ACTIVATE_RAGE, $ability->getTypeActivate());
        self::assertEquals(
            [
                WeaponTypeInterface::BOW,
                WeaponTypeInterface::CROSSBOW,
            ],
            $ability->getAllowedWeaponTypes()
        );

        $actions = $ability->getActions($enemyCommand, $command);

        self::assertCount(1, $actions);

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertEquals($name, $action->getNameAction());
            self::assertEquals($icon, $action->getIcon());
            // Проверка конвертации физического урона в урон магии смерти
            self::assertTrue($action->getOffense()->getDeathDamage() > 0);
        }
    }

    /**
     * Тест на применение способности Volley of Darkness
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param int $expectedDamage
     * @param int $expectedAccuracy
     * @param int $expectedCriticalChance
     * @param int $expectedCriticalMultiplier
     * @param int $expectedLifeSteal
     * @throws Exception
     */
    public function testVolleyOfDarknessAbilityUse(
        int $level,
        int $expectedDamage,
        int $expectedAccuracy,
        int $expectedCriticalChance,
        int $expectedCriticalMultiplier,
        int $expectedLifeSteal
    ): void
    {
        $unit = UnitFactory::createByTemplate(45);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->getAbility($unit, 'Volley of Darkness', $level);

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
            self::assertEquals(sprintf(self::MESSAGE_EN, $expectedDamage, $expectedLifeSteal), $this->getChat()->addMessage($action));
            self::assertEquals(sprintf(self::MESSAGE_RU, $expectedDamage, $expectedLifeSteal), $this->getChatRu()->addMessage($action));

            // Проверка вампиризма
            self::assertEquals(25, $action->getOffense()->getVampirism());

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
                34,
                280,
                7,
                240,
                8,
            ],
            [
                2,
                36,
                300,
                7,
                240,
                9,
            ],
            [
                3,
                38,
                320,
                7,
                240,
                9,
            ],
            [
                4,
                40,
                340,
                7,
                240,
                10,
            ],
            [
                5,
                42,
                360,
                7,
                240,
                10,
            ],
        ];
    }
}
