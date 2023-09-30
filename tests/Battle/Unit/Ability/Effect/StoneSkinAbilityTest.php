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

class StoneSkinAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/478.png" alt="" /> <span class="ability">Stone Skin</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/478.png" alt="" /> <span class="ability">Каменная кожа</span>';

    /**
     * Тест на создание способности Stone Skin через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testStoneSkinAbilityCreate(): void
    {
        $name = 'Stone Skin';
        $icon = '/images/icons/ability/478.png';

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
        self::assertFalse($ability->isDisposable());
        self::assertFalse($ability->isUsage());
        self::assertEquals(AbilityInterface::ACTIVATE_RAGE, $ability->getTypeActivate());
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
     * Тест на применение способности Stone Skin
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param int $expectedPhysicalResist
     * @param int $expectedFireResist
     * @param int $expectedWaterResist
     * @param int $expectedAirResist
     * @param int $expectedEarthResist
     * @param int $expectedLifeResist
     * @param int $expectedDeathResist
     * @param int $expectedFireMaxResist
     * @param int $expectedWaterMaxResist
     * @param int $expectedAirMaxResist
     * @param int $expectedEarthMaxResist
     * @param int $expectedEffectDuration
     * @throws Exception
     */
    public function testStoneSkinAbilityUse(
        int $level,
        int $expectedPhysicalResist,
        int $expectedFireResist,
        int $expectedWaterResist,
        int $expectedAirResist,
        int $expectedEarthResist,
        int $expectedLifeResist,
        int $expectedDeathResist,
        int $expectedFireMaxResist,
        int $expectedWaterMaxResist,
        int $expectedAirMaxResist,
        int $expectedEarthMaxResist,
        int $expectedEffectDuration
    ): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $statistics = new Statistic();

        $ability = $this->createAbilityByDataProvider($unit, 'Stone Skin', $level);

        $this->activateAbility($ability, $unit);

        self::assertTrue($ability->isReady());

        $actions = $ability->getActions($enemyCommand, $command);

        self::assertCount(1, $actions);

        foreach ($actions as $i => $action) {
            $scenario = new Scenario();

            self::assertInstanceOf(EffectAction::class, $action);
            self::assertTrue($action->canByUsed());
            $action->handle();

            // Сообщение об эффекте
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

        self::assertEquals($expectedPhysicalResist, $unit->getDefense()->getPhysicalResist());
        self::assertEquals($expectedFireResist, $unit->getDefense()->getFireResist());
        self::assertEquals($expectedWaterResist, $unit->getDefense()->getWaterResist());
        self::assertEquals($expectedAirResist, $unit->getDefense()->getAirResist());
        self::assertEquals($expectedEarthResist, $unit->getDefense()->getEarthResist());
        self::assertEquals($expectedLifeResist, $unit->getDefense()->getLifeResist());
        self::assertEquals($expectedDeathResist, $unit->getDefense()->getDeathResist());

        self::assertEquals($expectedFireMaxResist, $unit->getDefense()->getFireMaxResist());
        self::assertEquals($expectedWaterMaxResist, $unit->getDefense()->getWaterMaxResist());
        self::assertEquals($expectedAirMaxResist, $unit->getDefense()->getAirMaxResist());
        self::assertEquals($expectedEarthMaxResist, $unit->getDefense()->getEarthMaxResist());

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
                10,
                20,
                20,
                20,
                20,
                10,
                10,
                79,
                79,
                79,
                79,
                5,
            ],
            [
                2,
                11,
                22,
                22,
                22,
                22,
                11,
                11,
                80,
                80,
                80,
                80,
                6,
            ],
            [
                3,
                12,
                24,
                24,
                24,
                24,
                12,
                12,
                81,
                81,
                81,
                81,
                7,
            ],
            [
                4,
                13,
                26,
                26,
                26,
                26,
                13,
                13,
                82,
                82,
                82,
                82,
                8,
            ],
            [
                5,
                14,
                28,
                28,
                28,
                28,
                14,
                14,
                83,
                83,
                83,
                83,
                9,
            ],
        ];
    }
}
