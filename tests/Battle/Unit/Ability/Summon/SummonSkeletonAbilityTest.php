<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Summon;

use Battle\Action\ActionInterface;
use Battle\Unit\Ability\Ability;
use Battle\Unit\Ability\AbilityInterface;
use Exception;
use Battle\Action\SummonAction;
use Battle\Unit\Ability\Summon\SummonSkeletonAbility;
use Battle\Command\CommandFactory;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;
use Battle\Unit\Ability\AbilityCollection;

class SummonSkeletonAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_1</span> summon <img src="/images/icons/ability/338.png" alt="" /> <span class="ability">Skeleton</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_1</span> призвал <img src="/images/icons/ability/338.png" alt="" /> <span class="ability">Скелета</span>';

    /**
     * TODO В будущем этот тест будет удален вместе с классом SummonSkeletonAbility
     *
     * @throws Exception
     */
    public function testSummonSkeletonAbilityUse(): void
    {
        $name = 'Skeleton';
        $icon = '/images/icons/ability/338.png';
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new SummonSkeletonAbility($unit);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
        self::assertFalse($ability->isDisposable());
        self::assertFalse($ability->isUsage());

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $collection = new AbilityCollection();
        $collection->add($ability);

        foreach ($collection as $item) {
            self::assertEquals($ability, $item);
        }

        $collection->update($unit);

        self::assertTrue($ability->isReady());

        $actions = $ability->getAction($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertInstanceOf(SummonAction::class, $action);
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));
        }

        $ability->usage();

        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
        self::assertEquals(0, $unit->getConcentration());
    }

    /**
     * Тест на создание и применение способности SummonSkeletonAbility через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testNewSummonSkeletonAbilityUse(): void
    {
        $name = 'Skeleton';
        $icon = '/images/icons/ability/338.png';
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new Ability(
            $unit,
            false,
            $name,
            $icon,
            [
                [
                    'type'             => ActionInterface::SUMMON,
                    'action_unit'      => $unit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'name'             => $name,
                    'icon'             => $icon,
                    'summon'           => [
                        'name'       => $name,
                        'level'      => 1,
                        'avatar'     => '/images/icons/ability/338.png',
                        'life'       => 38,
                        'total_life' => 38,
                        'melee'      => true,
                        'class'      => null,
                        'race'       => 8,
                        'offense'    => [
                            'damage'       => 16,
                            'attack_speed' => 1,
                            'accuracy'     => 200,
                            'block_ignore' => 0,
                        ],
                        'defense'    => [
                            'defense' => 100,
                            'block'   => 0,
                        ],
                    ],
                ],
            ],
            AbilityInterface::ACTIVATE_CONCENTRATION,
            0
        );

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
        self::assertFalse($ability->isDisposable());
        self::assertFalse($ability->isUsage());

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $collection = new AbilityCollection();
        $collection->add($ability);

        foreach ($collection as $item) {
            self::assertEquals($ability, $item);
        }

        $collection->update($unit);

        self::assertTrue($ability->isReady());

        $actions = $ability->getAction($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertInstanceOf(SummonAction::class, $action);
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));
        }

        $ability->usage();

        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
        self::assertEquals(0, $unit->getConcentration());
    }
}
