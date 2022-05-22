<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Effect;

use Battle\Action\ActionCollection;
use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Effect\RageAbility;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class RageAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #ae882d">wounded_orc</span> use <img src="/images/icons/ability/285.png" alt="" /> <span class="ability">Rage</span>';
    private const MESSAGE_RU = '<span style="color: #ae882d">wounded_orc</span> использовал <img src="/images/icons/ability/285.png" alt="" /> <span class="ability">Ярость</span>';

    /**
     * Тест на создание способности RageAbility
     *
     * @throws Exception
     */
    public function testRageAbilityCreate(): void
    {
        $name = 'Rage';
        $icon = '/images/icons/ability/285.png';

        $unit = UnitFactory::createByTemplate(31);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new RageAbility($unit);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
        self::assertTrue($ability->isDisposable());
        self::assertFalse($ability->isUsage());

        // Активируем - созданный юнит изначально ранен и имеет здоровье < 30%
        $collection = new AbilityCollection();
        $collection->add($ability);

        foreach ($collection as $item) {
            self::assertEquals($ability, $item);
        }

        $collection->update($unit);

        self::assertTrue($ability->isReady());

        self::assertEquals(
            $this->getRageActions($unit, $enemyCommand, $command),
            $ability->getAction($enemyCommand, $command)
        );

        $ability->usage();
        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
    }

    /**
     * Тест на применение способности RageAbility
     *
     * @throws Exception
     */
    public function testRageAbilityApply(): void
    {
        $unit = UnitFactory::createByTemplate(31);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new RageAbility($unit);

        // Активируем - созданный юнит изначально сильно ранен и имеет здоровье < 30%
        $collection = new AbilityCollection();
        $collection->add($ability);

        foreach ($collection as $item) {
            self::assertEquals($ability, $item);
        }

        $collection->update($unit);

        self::assertTrue($ability->isReady());

        // Изначальный урон = 35
        self::assertEquals(35, $unit->getOffense()->getDamage());

        // Применяем способность
        foreach ($ability->getAction($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));
        }

        // Проверяем, что урон вырос в 2 раза
        self::assertEquals(70, $unit->getOffense()->getDamage());

        // Обновляем длительность эффектов. Длительность эффектов обновляется в getAfterActions()
        for ($i = 0; $i < 10; $i++) {
            foreach ($unit->getAfterActions() as $afterAction) {
                if ($afterAction->canByUsed()) {
                    $afterAction->handle();
                }
            }
        }

        // И проверяем, что урон вернулся к прежнему
        self::assertEquals(35, $unit->getOffense()->getDamage());
        self::assertCount(0, $unit->getEffects());
    }

    /**
     * Тест на проверку перехода события из способного к применению, в невозможное к применение и обратно
     *
     * @throws Exception
     */
    public function testRageAbilityCanByUsed(): void
    {
        $unit = UnitFactory::createByTemplate(31);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new RageAbility($unit);

        // Перед применением способности эффекта на юните еще нет - способность может быть применена
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        foreach ($ability->getAction($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        $ability->usage();

        // После появления эффекта на юните - способность уже не может быть применена
        self::assertFalse($ability->canByUsed($enemyCommand, $command));

        // Пропускаем ходы
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        // Эффект исчез - но способность не может быть опять применена, потому что она одноразовая
        self::assertFalse($ability->canByUsed($enemyCommand, $command));
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws Exception
     */
    private function getRageActions(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand
    ): ActionCollection
    {
        $actionFactory = new ActionFactory();
        $collection = new ActionCollection();

        $data = [
            'type'           => ActionInterface::EFFECT,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $alliesCommand,
            'type_target'    => ActionInterface::TARGET_SELF,
            'name'           => 'Rage',
            'icon'           => '/images/icons/ability/285.png',
            'message_method' => 'applyEffect',
            'effect'         => [
                'name'                  => 'Rage',
                'icon'                  => '/images/icons/ability/285.png',
                'duration'              => 8,
                'on_apply_actions'      => [
                    [
                        'type'           => ActionInterface::BUFF,
                        'action_unit'    => $unit,
                        'enemy_command'  => $enemyCommand,
                        'allies_command' => $alliesCommand,
                        'type_target'    => ActionInterface::TARGET_SELF,
                        'name'           => 'Rage',
                        'modify_method'  => 'multiplierDamage',
                        'power'          => 200,
                        'message_method' => ActionInterface::SKIP_MESSAGE_METHOD,
                    ],
                ],
                'on_next_round_actions' => [],
                'on_disable_actions'    => [],
            ],
        ];

        $collection->add($actionFactory->create($data));

        return $collection;
    }
}
