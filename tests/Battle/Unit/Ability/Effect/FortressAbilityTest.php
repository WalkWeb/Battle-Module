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

class FortressAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EFFECT_EN = '<span style="color: #1e72e3">wounded_unit</span> restored %d life from effect <img src="/images/icons/ability/409.png" alt="" /> <span class="ability">Fortress</span>';
    private const MESSAGE_EFFECT_RU = '<span style="color: #1e72e3">wounded_unit</span> восстановил %d здоровья от эффекта <img src="/images/icons/ability/409.png" alt="" /> <span class="ability">Крепость</span>';

    private const MESSAGE_EN = '<span style="color: #1e72e3">wounded_unit</span> use <img src="/images/icons/ability/409.png" alt="" /> <span class="ability">Fortress</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">wounded_unit</span> использовал <img src="/images/icons/ability/409.png" alt="" /> <span class="ability">Крепость</span>';

    /**
     * Тест на создание способности Fortress через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testFortressAbilityCreate(): void
    {
        $name = 'Fortress';
        $icon = '/images/icons/ability/409.png';

        $unit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
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

        self::assertCount(1, $actions);

        foreach ($ability->getActions($enemyCommand, $command) as $i => $action) {
            self::assertInstanceOf(EffectAction::class, $action);

            self::assertCount(14, $action->getEffect()->getOnApplyActions());

            foreach ($action->getEffect()->getOnApplyActions() as $effect) {
                self::assertInstanceOf(BuffAction::class, $effect);
                self::assertEquals($name, $action->getNameAction());
                self::assertEquals($icon, $action->getIcon());
            }

            self::assertCount(1, $action->getEffect()->getOnNextRoundActions());

            foreach ($action->getEffect()->getOnNextRoundActions() as $effect) {
                self::assertInstanceOf(HealAction::class, $effect);
                self::assertEquals($name, $action->getNameAction());
                self::assertEquals($icon, $action->getIcon());
            }
        }
    }

    /**
     * Тест на применение способности Fortress
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
    public function testFortressAbilityUse(
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
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $statistics = new Statistic();

        $ability = $this->getAbility($unit, 'Fortress', $level);

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

            self::assertCount(1, $unit->getBeforeActions());

            foreach ($unit->getBeforeActions() as $effectAction) {
                self::assertInstanceOf(HealAction::class, $effectAction);
                self::assertTrue($effectAction->canByUsed());
                $effectAction->handle();
                self::assertEquals($expectedHealPower, $effectAction->getFactualPower());
                self::assertEquals(sprintf(self::MESSAGE_EFFECT_EN, $expectedHealPower), $this->getChat()->addMessage($effectAction));
                self::assertEquals(sprintf(self::MESSAGE_EFFECT_RU, $expectedHealPower), $this->getChatRu()->addMessage($effectAction));
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
                16,
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
                5,
            ],
            [
                2,
                23,
                22,
                22,
                22,
                22,
                22,
                22,
                22,
                78,
                78,
                78,
                78,
                78,
                78,
                78,
                6,
            ],
//            [
//                3,
//                32,
//                24,
//                24,
//                24,
//                24,
//                24,
//                24,
//                24,
//                79,
//                79,
//                79,
//                79,
//                79,
//                79,
//                79,
//                7,
//            ],
//            [
//                4,
//                39,
//                26,
//                26,
//                26,
//                26,
//                26,
//                26,
//                26,
//                79,
//                79,
//                79,
//                79,
//                79,
//                79,
//                79,
//                8,
//            ],
//            [
//                5,
//                49,
//                28,
//                28,
//                28,
//                28,
//                28,
//                28,
//                28,
//                80,
//                80,
//                80,
//                80,
//                80,
//                80,
//                80,
//                9,
//            ],
        ];
    }
}
