<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Effect;

use Battle\Action\BuffAction;
use Battle\Action\EffectAction;
use Battle\Action\HealAction;
use Battle\Command\CommandFactory;
use Battle\Response\Scenario\Scenario;
use Battle\Response\Statistic\Statistic;
use Battle\Unit\Ability\AbilityInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class VanguardAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_HEAL_EN = '<span style="color: #1e72e3">wounded_unit</span> use <img src="/images/icons/ability/253.png" alt="" /> <span class="ability">Vanguard</span> and healed itself on %d life';
    private const MESSAGE_HEAL_RU = '<span style="color: #1e72e3">wounded_unit</span> использовал <img src="/images/icons/ability/253.png" alt="" /> <span class="ability">Авангард</span> и вылечил себя на %d здоровья';

    private const MESSAGE_EN = '<span style="color: #1e72e3">wounded_unit</span> use <img src="/images/icons/ability/253.png" alt="" /> <span class="ability">Vanguard</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">wounded_unit</span> использовал <img src="/images/icons/ability/253.png" alt="" /> <span class="ability">Авангард</span>';

    /**
     * Тест на создание способности Vanguard через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testVanguardAbilityCreate(): void
    {
        $name = 'Vanguard';
        $icon = '/images/icons/ability/253.png';

        $unit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->getAbility($unit, $name, 1);

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

        self::assertCount(2, $actions);

        foreach ($ability->getActions($enemyCommand, $command) as $i => $action) {
            if ($i === 0) {
                self::assertInstanceOf(HealAction::class, $action);
                self::assertEquals($name, $action->getNameAction());
                self::assertEquals($icon, $action->getIcon());
            }
            if ($i === 1) {
                self::assertInstanceOf(EffectAction::class, $action);
                self::assertEquals($name, $action->getNameAction());
                self::assertEquals($icon, $action->getIcon());
                foreach ($action->getEffect()->getOnNextRoundActions() as $effectDamage) {
                    self::assertInstanceOf(BuffAction::class, $effectDamage);
                    self::assertEquals($name, $effectDamage->getNameAction());
                    self::assertEquals($icon, $effectDamage->getIcon());
                }
            }
        }
    }

    /**
     * Тест на применение способности Vanguard
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param int $expectedHealPower
     * @param int $expectedPhysicalResist
     * @param int $expectedFireResist
     * @param int $expectedWaterResist
     * @param int $expectedAirResist
     * @param int $expectedEarthResist
     * @param int $expectedLifeResist
     * @param int $expectedDeathResist
     * @param int $expectedPhysicalMaxResist
     * @param int $expectedFireMaxResist
     * @param int $expectedWaterMaxResist
     * @param int $expectedAirMaxResist
     * @param int $expectedEarthMaxResist
     * @param int $expectedLifeMaxResist
     * @param int $expectedDeathMaxResist
     * @param int $expectedEffectDuration
     * @throws Exception
     */
    public function testVanguardAbilityUse(
        int $level,
        int $expectedHealPower,
        int $expectedPhysicalResist,
        int $expectedFireResist,
        int $expectedWaterResist,
        int $expectedAirResist,
        int $expectedEarthResist,
        int $expectedLifeResist,
        int $expectedDeathResist,
        int $expectedPhysicalMaxResist,
        int $expectedFireMaxResist,
        int $expectedWaterMaxResist,
        int $expectedAirMaxResist,
        int $expectedEarthMaxResist,
        int $expectedLifeMaxResist,
        int $expectedDeathMaxResist,
        int $expectedEffectDuration
    ): void
    {
        $unit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $statistics = new Statistic();

        $ability = $this->getAbility($unit, 'Vanguard', $level);

        $this->activateAbility($ability, $unit);

        self::assertTrue($ability->isReady());

        $actions = $ability->getActions($enemyCommand, $command);

        self::assertCount(2, $actions);

        foreach ($actions as $i => $action) {
            if ($i === 0) {
                $scenario = new Scenario();

                self::assertInstanceOf(HealAction::class, $action);
                self::assertTrue($action->canByUsed());
                $action->handle();

                // Сообщение об эффекте
                self::assertEquals(sprintf(self::MESSAGE_HEAL_EN, $expectedHealPower), $this->getChat()->addMessage($action));
                self::assertEquals(sprintf(self::MESSAGE_HEAL_RU, $expectedHealPower), $this->getChatRu()->addMessage($action));

                // Проверяем что создана анимация применения эффекта
                $scenario->addAnimation($action, $statistics);
                self::assertCount(1, $scenario->getArray());
            }
            if ($i === 1) {
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

        self::assertEquals($expectedPhysicalMaxResist, $unit->getDefense()->getPhysicalMaxResist());
        self::assertEquals($expectedFireMaxResist, $unit->getDefense()->getFireMaxResist());
        self::assertEquals($expectedWaterMaxResist, $unit->getDefense()->getWaterMaxResist());
        self::assertEquals($expectedAirMaxResist, $unit->getDefense()->getAirMaxResist());
        self::assertEquals($expectedEarthMaxResist, $unit->getDefense()->getEarthMaxResist());
        self::assertEquals($expectedLifeMaxResist, $unit->getDefense()->getLifeMaxResist());
        self::assertEquals($expectedDeathMaxResist, $unit->getDefense()->getDeathMaxResist());

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
                80,
                12,
                12,
                12,
                12,
                12,
                12,
                12,
                77,
                77,
                77,
                77,
                77,
                77,
                77,
                5,
            ],
            [
                2,
                140,
                14,
                14,
                14,
                14,
                14,
                14,
                14,
                77,
                77,
                77,
                77,
                77,
                77,
                77,
                6,
            ],
            [
                3,
                229,
                16,
                16,
                16,
                16,
                16,
                16,
                16,
                77,
                77,
                77,
                77,
                77,
                77,
                77,
                7,
            ],
            [
                4,
                314,
                18,
                18,
                18,
                18,
                18,
                18,
                18,
                78,
                78,
                78,
                78,
                78,
                78,
                78,
                8,
            ],
            [
                5,
                443,
                20,
                20,
                20,
                20,
                20,
                20,
                20,
                78,
                78,
                78,
                78,
                78,
                78,
                78,
                9,
            ],
        ];
    }
}
