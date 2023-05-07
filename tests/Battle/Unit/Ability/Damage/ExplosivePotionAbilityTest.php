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

class ExplosivePotionAbilityTest extends AbstractAbilityTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_4</span> use <img src="/images/icons/ability/390.png" alt="" /> <span class="ability">Explosive Potion</span> and hit for %d damage against <span style="color: #1e72e3">unit_2</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_4</span> использовал <img src="/images/icons/ability/390.png" alt="" /> <span class="ability">Взрывчатое зелье</span> и нанес удар на %d урона по <span style="color: #1e72e3">unit_2</span>';

    /**
     * Тест на создание способности Explosive Potion через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testExplosivePotionAbilityCreate(): void
    {
        $name = 'Explosive Potion';
        $icon = '/images/icons/ability/390.png';
        $disposable = false;

        $unit = UnitFactory::createByTemplate(4);
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
            // Проверка конвертации физического урона в урон огнем
            self::assertTrue($action->getOffense()->getFireDamage() > 0);
        }
    }

    /**
     * Тест на применение способности Explosive Potion
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param int $expectedDamage
     * @param int $expectedAccuracy
     * @param int $expectedCriticalChance
     * @param int $expectedCriticalMultiplier
     * @throws Exception
     */
    public function testExplosivePotionAbilityUse(
        int $level,
        int $expectedDamage,
        int $expectedAccuracy,
        int $expectedCriticalChance,
        int $expectedCriticalMultiplier
    ): void
    {
        $unit = UnitFactory::createByTemplate(4);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, 'Explosive Potion', $level);

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
                21,
                320,
                2,
                100,
            ],
            [
                2,
                22,
                340,
                2,
                100,
            ],
            [
                3,
                24,
                360,
                2,
                100,
            ],
            [
                4,
                25,
                380,
                2,
                100,
            ],
            [
                5,
                27,
                400,
                2,
                100,
            ],
        ];
    }
}
