<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Resurrection;

use Battle\Action\ActionCollection;
use Battle\Action\ResurrectionAction;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\Resurrection\WillToLiveAbility;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class WillToLiveAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">dead_unit</span> use <img src="/images/icons/ability/429.png" alt="" /> <span class="ability">Will to live</span> and resurrected <span style="color: #1e72e3">dead_unit</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">dead_unit</span> использовал <img src="/images/icons/ability/429.png" alt="" /> <span class="ability">Воля к жизни</span> и воскресил <span style="color: #1e72e3">dead_unit</span>';

    /**
     * Тест на создание способности WillToLiveAbility
     *
     * @throws Exception
     */
    public function testWillToLiveAbilityCreate(): void
    {
        $name = 'Will to live';
        $icon = '/images/icons/ability/429.png';

        $unit = UnitFactory::createByTemplate(10);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new WillToLiveAbility($unit);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        // Активируем - созданный юнит изначально мертв
        $ability->update($unit, true);

        self::assertTrue($ability->isReady());

        self::assertEquals(
            $this->getWillToLiveActions($unit, $enemyCommand, $command),
            $ability->getAction($enemyCommand, $command)
        );

        $ability->usage();

        self::assertFalse($ability->isReady());
    }

    /**
     * Тест на применение способности WillToLiveAbility
     *
     * @throws Exception
     */
    public function testWillToLiveAbilityApply(): void
    {
        $unit = UnitFactory::createByTemplate(10);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new WillToLiveAbility($unit);

        // Изначально юнит мертв
        self::assertEquals(0, $unit->getLife());

        // Активируем способность
        // Вначале используем метод в обычном режиме - для 100% покрытия кода тестами
        $ability->update($unit);
        // Но чтобы активировать её точно - используем в тестовом режиме
        $ability->update($unit, true);

        // Применяем способность
        foreach ($ability->getAction($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));
        }

        $ability->usage();

        // Здоровье восстановилось
        self::assertEquals($unit->getTotalLife() / 2, $unit->getLife());

        self::assertFalse($ability->isReady());
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @return ActionCollection
     * @throws Exception
     */
    private function getWillToLiveActions(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command
    ): ActionCollection
    {
        $actionCollection = new ActionCollection();

        $actionCollection->add(new ResurrectionAction(
            $unit,
            $enemyCommand,
            $command,
            ResurrectionAction::TARGET_SELF,
            50,
            'Will to live',
            '/images/icons/ability/429.png'
        ));

        return $actionCollection;
    }
}
