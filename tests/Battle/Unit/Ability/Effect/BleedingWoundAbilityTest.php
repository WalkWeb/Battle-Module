<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Effect;

use Battle\Action\DamageAction;
use Battle\Action\EffectAction;
use Battle\Command\CommandFactory;
use Battle\Response\Scenario\Scenario;
use Battle\Response\Statistic\Statistic;
use Battle\Weapon\Type\WeaponTypeInterface;
use Exception;
use Tests\Battle\Unit\Ability\AbstractAbilityTest;
use Tests\Factory\UnitFactory;

class BleedingWoundAbilityTest extends AbstractAbilityTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">100_dodge</span> use <img src="/images/icons/ability/438.png" alt="" /> <span class="ability">Bleeding Wound</span> and hit for %d damage against <span style="color: #1e72e3">unit_2</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">100_dodge</span> использовал <img src="/images/icons/ability/438.png" alt="" /> <span class="ability">Кровоточащая рана</span> и нанес удар на %d урона по <span style="color: #1e72e3">unit_2</span>';

    private const MESSAGE_EFFECT_EN = '<span style="color: #1e72e3">unit_2</span> received %d damage from effect <img src="/images/icons/ability/438.png" alt="" /> <span class="ability">Bleeding Wound</span>';
    private const MESSAGE_EFFECT_RU = '<span style="color: #1e72e3">unit_2</span> получил %d урона от эффекта <img src="/images/icons/ability/438.png" alt="" /> <span class="ability">Кровоточащая рана</span>';

    /**
     * Тест на создание способности Bleeding Wound через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testBleedingWoundAbilityCreate(): void
    {
        $name = 'Bleeding Wound';
        $icon = '/images/icons/ability/438.png';
        $disposable = false;

        $unit = UnitFactory::createByTemplate(51);
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
        self::assertEquals([
            WeaponTypeInterface::DAGGER,
        ],
            $ability->getAllowedWeaponTypes()
        );

        $actions = $ability->getActions($enemyCommand, $command);

        self::assertCount(2, $actions);

        foreach ($ability->getActions($enemyCommand, $command) as $i => $action) {
            self::assertEquals($name, $action->getNameAction());
            self::assertEquals($icon, $action->getIcon());
            if ($i === 0) {
                self::assertInstanceOf(DamageAction::class, $action);
                // Проверка отсутствия конвертации физического урона
                self::assertTrue($action->getOffense()->getPhysicalDamage() > 0);
            }
            if ($i === 1) {
                self::assertInstanceOf(EffectAction::class, $action);
            }
        }
    }

    /**
     * Тест на применение способности Bleeding Wound
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param int $expectedDamage
     * @param int $expectedAccuracy
     * @param int $expectedEffectDamage
     * @param int $expectedEffectDuration
     * @throws Exception
     */
    public function testBleedingWoundAbilityUse(
        int $level,
        int $expectedDamage,
        int $expectedAccuracy,
        int $expectedEffectDamage,
        int $expectedEffectDuration
    ): void
    {
        $unit = UnitFactory::createByTemplate(51);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $statistics = new Statistic();

        $ability = $this->createAbilityByDataProvider($unit, 'Bleeding Wound', $level);

        $this->activateAbility($ability, $unit);

        self::assertTrue($ability->isReady());

        $actions = $ability->getActions($enemyCommand, $command);

        self::assertCount(2, $actions);

        foreach ($actions as $i => $action) {
            $scenario = new Scenario();
            if ($i === 0) {
                self::assertInstanceOf(DamageAction::class, $action);
                self::assertTrue($action->canByUsed());
                $action->handle();
                self::assertEquals($expectedDamage, $action->getFactualPower());
                self::assertEquals($expectedAccuracy, $action->getOffense()->getAccuracy());
                self::assertEquals(sprintf(self::MESSAGE_EN, $expectedDamage), $this->getChat()->addMessage($action));
                self::assertEquals(sprintf(self::MESSAGE_RU, $expectedDamage), $this->getChatRu()->addMessage($action));

                // Дополнительное проверяем, что по событию успешно создается анимация
                $scenario->addAnimation($action, $statistics);
                self::assertCount(1, $scenario->getArray());
            }
            if ($i === 1) {
                self::assertInstanceOf(EffectAction::class, $action);
                self::assertTrue($action->canByUsed());
                $action->handle();

                // Сообщений об эффекте не формируется
                self::assertEquals('', $this->getChat()->addMessage($action));
                self::assertEquals('', $this->getChatRu()->addMessage($action));

                // Анимации также не должно быть
                $scenario->addAnimation($action, $statistics);
                self::assertCount(0, $scenario->getArray());

                // Проверяем эффект
                self::assertCount(1, $enemyUnit->getEffects());
                self::assertCount(1, $enemyUnit->getBeforeActions());

                foreach ($enemyUnit->getEffects() as $effect) {
                    self::assertEquals($expectedEffectDuration, $effect->getBaseDuration());
                }

                foreach ($enemyUnit->getBeforeActions() as $effectAction) {
                    self::assertInstanceOf(DamageAction::class, $effectAction);
                    self::assertTrue($effectAction->canByUsed());
                    $effectAction->handle();
                    self::assertEquals($expectedEffectDamage, $effectAction->getFactualPower());
                    self::assertEquals(sprintf(self::MESSAGE_EFFECT_EN, $expectedEffectDamage), $this->getChat()->addMessage($effectAction));
                    self::assertEquals(sprintf(self::MESSAGE_EFFECT_RU, $expectedEffectDamage), $this->getChatRu()->addMessage($effectAction));
                }
            }
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
                20,
                280,
                4,
                4,
            ],
            [
                2,
                21,
                300,
                4,
                4,
            ],
            [
                3,
                22,
                320,
                4,
                5,
            ],
            [
                4,
                23,
                340,
                4,
                5,
            ],
            [
                5,
                24,
                360,
                4,
                5,
            ],
        ];
    }
}
