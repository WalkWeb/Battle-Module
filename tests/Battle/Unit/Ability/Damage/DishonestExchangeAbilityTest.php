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

class DishonestExchangeAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_4</span> use <img src="/images/icons/ability/160.png" alt="" /> <span class="ability">Dishonest Exchange</span> and hit for %d damage against <span style="color: #1e72e3">unit_2</span> and restore %d life';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_4</span> использовал <img src="/images/icons/ability/160.png" alt="" /> <span class="ability">Нечестный обмен</span> и нанес удар на %d урона по <span style="color: #1e72e3">unit_2</span> и восстановил %d здоровья';

    /**
     * Тест на создание способности Dishonest Exchange через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testDishonestExchangeAbilityCreate(): void
    {
        $name = 'Dishonest Exchange';
        $icon = '/images/icons/ability/160.png';
        $disposable = false;

        $unit = UnitFactory::createByTemplate(4);
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
                WeaponTypeInterface::STAFF,
                WeaponTypeInterface::WAND,
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
     * Тест на применение способности Dishonest Exchange
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param int $expectedDamage
     * @param int $expectedAccuracy
     * @param int $expectedLifeSteal
     * @throws Exception
     */
    public function testDishonestExchangeAbilityUse(
        int $level,
        int $expectedDamage,
        int $expectedAccuracy,
        int $expectedLifeSteal
    ): void
    {
        $unit = UnitFactory::createByTemplate(4);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->getAbility($unit, 'Dishonest Exchange', $level);

        $this->activateAbility($ability, $unit);

        self::assertTrue($ability->isReady());

        $actions = $ability->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertInstanceOf(DamageAction::class, $action);
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals($expectedDamage, $action->getFactualPower());
            self::assertEquals($expectedAccuracy, $action->getOffense()->getAccuracy());
            self::assertEquals(sprintf(self::MESSAGE_EN, $expectedDamage, $expectedLifeSteal), $this->getChat()->addMessage($action));
            self::assertEquals(sprintf(self::MESSAGE_RU, $expectedDamage, $expectedLifeSteal), $this->getChatRu()->addMessage($action));

            // Проверка вампиризма
            self::assertEquals(100, $action->getOffense()->getVampirism());

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
                9,
                240,
                9,
            ],
            [
                2,
                9,
                260,
                9,
            ],
            [
                3,
                10,
                280,
                10,
            ],
            [
                4,
                11,
                300,
                11,
            ],
            [
                5,
                12,
                320,
                12,
            ],
        ];
    }
}
