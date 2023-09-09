<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Effect;

use Battle\Action\BuffAction;
use Battle\Action\EffectAction;
use Battle\Command\CommandFactory;
use Battle\Response\Scenario\Scenario;
use Battle\Response\Statistic\Statistic;
use Battle\Unit\Ability\AbilityInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class UnityWithWindAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_4</span> use <img src="/images/icons/ability/500.png" alt="" /> <span class="ability">Unity with Wind</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_4</span> использовал <img src="/images/icons/ability/500.png" alt="" /> <span class="ability">Единство с ветром</span>';

    /**
     * Тест на создание способности Unity with Wind через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testUnityWithWindAbilityCreate(): void
    {
        $name = 'Unity with Wind';
        $icon = '/images/icons/ability/500.png';
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
        self::assertEquals(AbilityInterface::ACTIVATE_CUNNING, $ability->getTypeActivate());
        self::assertEquals([], $ability->getAllowedWeaponTypes());

        $actions = $ability->getActions($enemyCommand, $command);

        self::assertCount(1, $actions);

        foreach ($ability->getActions($enemyCommand, $command) as $i => $action) {
            self::assertInstanceOf(EffectAction::class, $action);
            foreach ($action->getEffect()->getOnNextRoundActions() as $effectDamage) {
                self::assertInstanceOf(BuffAction::class, $effectDamage);
                self::assertEquals($name, $action->getNameAction());
                self::assertEquals($icon, $action->getIcon());
            }
        }
    }

    /**
     * Тест на применение способности Unity with Wind
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param float $expectedAttackSpeed
     * @param float $expectedCastSpeed
     * @param int $expectedAccuracy
     * @param int $expectedMagicAccuracy
     * @param int $expectedDefense
     * @param int $expectedMagicDefense
     * @param int $expectedEffectDuration
     * @throws Exception
     */
    public function testUnityWithWindAbilityUse(
        int $level,
        float $expectedAttackSpeed,
        float $expectedCastSpeed,
        int $expectedAccuracy,
        int $expectedMagicAccuracy,
        int $expectedDefense,
        int $expectedMagicDefense,
        int $expectedEffectDuration
    ): void
    {
        $unit = UnitFactory::createByTemplate(4);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $statistics = new Statistic();

        $ability = $this->createAbilityByDataProvider($unit, 'Unity with Wind', $level);

        $this->activateAbility($ability, $unit);

        self::assertTrue($ability->isReady());

        $actions = $ability->getActions($enemyCommand, $command);

        self::assertCount(1, $actions);

        foreach ($actions as $i => $action) {
            $scenario = new Scenario();

            self::assertInstanceOf(EffectAction::class, $action);
            self::assertTrue($action->canByUsed());
            $action->handle();

            // Сообщений об эффекте
            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));

            // Проверяем что создана анимация применения эффекта
            $scenario->addAnimation($action, $statistics);
            self::assertCount(1, $scenario->getArray());
        }

        // Проверяем наличие эффекта
        self::assertCount(1, $unit->getEffects());

        // Проверяем длительность эффекта
        foreach ($unit->getEffects() as $effect) {
            self::assertEquals($expectedEffectDuration, $effect->getDuration());
        }

        // Проверяем обновленные параметры
        self::assertEquals($expectedAttackSpeed, $unit->getOffense()->getAttackSpeed());
        self::assertEquals($expectedCastSpeed, $unit->getOffense()->getCastSpeed());
        self::assertEquals($expectedAccuracy, $unit->getOffense()->getAccuracy());
        self::assertEquals($expectedMagicAccuracy, $unit->getOffense()->getMagicAccuracy());
        self::assertEquals($expectedDefense, $unit->getDefense()->getDefense());
        self::assertEquals($expectedMagicDefense, $unit->getDefense()->getMagicDefense());

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
                1.1,
                1.32,
                220,
                110,
                110,
                55,
                5,
            ],
            [
                2,
                1.12,
                1.34,
                224,
                112,
                112,
                56,
                5,
            ],
            [
                3,
                1.14,
                1.37,
                227,
                113,
                113,
                56,
                6,
            ],
            [
                4,
                1.16,
                1.39,
                231,
                115,
                115,
                57,
                6,
            ],
            [
                5,
                1.18,
                1.42,
                236,
                118,
                118,
                59,
                7,
            ],
        ];
    }
}
