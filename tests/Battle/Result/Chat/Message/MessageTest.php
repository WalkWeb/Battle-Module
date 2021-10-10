<?php

declare(strict_types=1);

namespace Tests\Battle\Result\Chat\Message;

use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Action\BuffAction;
use Battle\Action\DamageAction;
use Battle\Action\EffectAction;
use Battle\Action\HealAction;
use Battle\Action\ResurrectionAction;
use Battle\Action\SummonAction;
use Battle\Action\WaitAction;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Container\Container;
use Battle\Container\ContainerException;
use Battle\Container\ContainerInterface;
use Battle\Result\Chat\Message\Message;
use Battle\Result\Chat\Message\MessageException;
use Battle\Translation\Translation;
use Battle\Unit\UnitInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\BaseFactory;
use Tests\Battle\Factory\UnitFactory;

class MessageTest extends TestCase
{
    private const DAMAGE_EN = '<span style="color: #1e72e3">unit_1</span> attack <span style="color: #1e72e3">unit_2</span> on 20 damage';
    private const DAMAGE_RU = '<span style="color: #1e72e3">unit_1</span> атаковал <span style="color: #1e72e3">unit_2</span> на 20 урона';

    private const HEAL_EN = '<span style="color: #1e72e3">unit_1</span> heal <span style="color: #1e72e3">wounded_unit</span> on 20 life';
    private const HEAL_RU = '<span style="color: #1e72e3">unit_1</span> вылечил <span style="color: #1e72e3">wounded_unit</span> на 20 здоровья';

    private const SUMMON_EN = '<span style="color: #1e72e3">unit_1</span> summon Imp';
    private const SUMMON_RU = '<span style="color: #1e72e3">unit_1</span> призвал Беса';

    private const WAIT_EN = '<span style="color: #1e72e3">unit_1</span> preparing to attack';
    private const WAIT_RU = '<span style="color: #1e72e3">unit_1</span> готовится к атаке';

    private const EFFECT_DAMAGE_EN = '<span style="color: #1e72e3">unit_1</span> received damage on 10 life from effect Poison';
    private const EFFECT_DAMAGE_RU = '<span style="color: #1e72e3">unit_1</span> получил урон на 10 здоровья от эффекта Отравление';

    private const BUFF_EN = '<span style="color: #1e72e3">unit_1</span> use Reserve Forces';
    private const BUFF_RU = '<span style="color: #1e72e3">unit_1</span> использовал Резервные Силы';

    private const RESURRECTION_EN = '<span style="color: #1e72e3">unit_1</span> resurrection <span style="color: #1e72e3">dead_unit</span>';
    private const RESURRECTION_RU = '<span style="color: #1e72e3">unit_1</span> воскресил <span style="color: #1e72e3">dead_unit</span>';

    private const EFFECT_HEAL_EN = '<span style="color: #1e72e3">wounded_unit</span> restored 15 life from effect Healing Potion';
    private const EFFECT_HEAL_RU = '<span style="color: #1e72e3">wounded_unit</span> восстановил 15 здоровья от эффекта Лечебное зелье';

    private const APPLY_EFFECT_SELF_EN = '<span style="color: #1e72e3">unit_1</span> use Reserve Forces';
    private const APPLY_EFFECT_SELF_RU = '<span style="color: #1e72e3">unit_1</span> использовал Резервные Силы';

    private const APPLY_EFFECT_TO_EN = '<span style="color: #1e72e3">unit_1</span> use Reserve Forces on <span style="color: #1e72e3">unit_2</span>';
    private const APPLY_EFFECT_TO_RU = '<span style="color: #1e72e3">unit_1</span> использовал Резервные Силы на <span style="color: #1e72e3">unit_2</span>';

    /**
     * Тест на формирование сообщения об уроне, на английском
     *
     * @throws Exception
     */
    public function testMessageDamageEn(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $action = new DamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        self::assertTrue($action->canByUsed());
        self::assertEquals(self::DAMAGE_EN, $action->handle());
    }

    /**
     * Тест на формирование сообщения об уроне, на русском
     *
     * @throws Exception
     */
    public function testMessageDamageRu(): void
    {
        $container = $this->getContainerWithRuLanguage();

        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2, $container);

        $action = new DamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        self::assertTrue($action->canByUsed());
        self::assertEquals(self::DAMAGE_RU, $action->handle());
    }

    /**
     * Тест на формирование сообщения о лечении, на английском
     *
     * @throws Exception
     */
    public function testMessageHealEn(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $otherUnit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit, $otherUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new HealAction($unit, $enemyCommand, $command, HealAction::TARGET_WOUNDED_ALLIES);

        self::assertTrue($action->canByUsed());
        self::assertEquals(self::HEAL_EN, $action->handle());
    }

    /**
     * Тест на формирование сообщения о лечении, на английском
     *
     * @throws Exception
     */
    public function testMessageHealRu(): void
    {
        $container = $this->getContainerWithRuLanguage();

        $unit = UnitFactory::createByTemplate(1, $container);
        $otherUnit = UnitFactory::createByTemplate(11, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $command = CommandFactory::create([$unit, $otherUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new HealAction($unit, $enemyCommand, $command, HealAction::TARGET_WOUNDED_ALLIES);

        self::assertTrue($action->canByUsed());
        self::assertEquals(self::HEAL_RU, $action->handle());
    }

    /**
     * Тест на формирование сообщения о призыве, на английском
     *
     * @throws Exception
     */
    public function testMessageSummonEn(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);
        $summon = UnitFactory::createByTemplate(18);

        $action = new SummonAction(
            $unit,
            $enemyCommand,
            $command,
            'summon Imp',
            $summon
        );

        self::assertTrue($action->canByUsed());
        self::assertEquals(self::SUMMON_EN, $action->handle());
    }

    /**
     * Тест на формирование сообщения о призыве, на русском
     *
     * @throws Exception
     */
    public function testMessageSummonRu(): void
    {
        $container = $this->getContainerWithRuLanguage();

        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2, $container);
        $summon = UnitFactory::createByTemplate(18);

        $action = new SummonAction(
            $unit,
            $enemyCommand,
            $command,
            'summon Imp',
            $summon
        );

        self::assertTrue($action->canByUsed());
        self::assertEquals(self::SUMMON_RU, $action->handle());
    }

    /**
     * Тест на формирование сообщения о пропуске хода, на английском
     *
     * @throws Exception
     */
    public function testMessageWaitEn(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $action = new WaitAction($unit, $enemyCommand, $command);

        self::assertTrue($action->canByUsed());
        self::assertEquals(self::WAIT_EN, $action->handle());
    }

    /**
     * Тест на формирование сообщения о пропуске хода, на русском
     *
     * @throws Exception
     */
    public function testMessageWaitRu(): void
    {
        $container = $this->getContainerWithRuLanguage();

        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2, $container);

        $action = new WaitAction($unit, $enemyCommand, $command);

        self::assertTrue($action->canByUsed());
        self::assertEquals(self::WAIT_RU, $action->handle());
    }

    /**
     * Тест на формирование сообщения о применении эффекта на себя, на английском
     *
     * @throws Exception
     */
    public function testMessageApplyEffectToSelfEn(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $effectAction = $this->getReserveForcesAction($unit, $enemyCommand, $command, EffectAction::TARGET_SELF);

        self::assertTrue($effectAction->canByUsed());
        self::assertEquals(self::APPLY_EFFECT_SELF_EN, $effectAction->handle());
    }

    /**
     * Тест на формирование сообщения о применении эффекта на себя, на русском
     *
     * @throws Exception
     */
    public function testMessageApplyEffectToSelfRu(): void
    {
        $container = $this->getContainerWithRuLanguage();

        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2, $container);

        $effectAction = $this->getReserveForcesAction($unit, $enemyCommand, $command, EffectAction::TARGET_SELF);

        self::assertTrue($effectAction->canByUsed());
        self::assertEquals(self::APPLY_EFFECT_SELF_RU, $effectAction->handle());
    }

    /**
     * Тест на формирование сообщения о применении эффекта на другого юнита, на английском
     *
     * @throws Exception
     */
    public function testMessageApplyEffectToOtherEn(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $effectAction = $this->getReserveForcesAction($unit, $enemyCommand, $command, EffectAction::TARGET_RANDOM_ENEMY);

        self::assertTrue($effectAction->canByUsed());
        self::assertEquals(self::APPLY_EFFECT_TO_EN, $effectAction->handle());
    }

    /**
     * Тест на формирование сообщения о применении эффекта на другого юнита, на русском
     *
     * @throws Exception
     */
    public function testMessageApplyEffectToOtherRu(): void
    {
        $container = $this->getContainerWithRuLanguage();

        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2, $container);

        $effectAction = $this->getReserveForcesAction($unit, $enemyCommand, $command, EffectAction::TARGET_RANDOM_ENEMY);

        self::assertTrue($effectAction->canByUsed());
        self::assertEquals(self::APPLY_EFFECT_TO_RU, $effectAction->handle());
    }

    /**
     * Тест на формирование сообщения об усилении, на английском
     *
     * @throws Exception
     */
    public function testMessageBuffEn(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $action = new BuffAction(
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'use Reserve Forces',
            'multiplierMaxLife',
            130
        );

        self::assertTrue($action->canByUsed());
        self::assertEquals(self::BUFF_EN, $action->handle());
    }

    /**
     * Тест на формирование сообщения об усилении, на русском
     *
     * @throws Exception
     */
    public function testMessageBuffRu(): void
    {
        $container = $this->getContainerWithRuLanguage();

        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2, $container);

        $action = new BuffAction(
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'use Reserve Forces',
            'multiplierMaxLife',
            130
        );

        self::assertTrue($action->canByUsed());
        self::assertEquals(self::BUFF_RU, $action->handle());
    }

    /**
     * Тест на формирование сообщения о воскрешении, на английском
     *
     *  @throws Exception
     */
    public function testMessageResurrectionEn(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $deadUnit = UnitFactory::createByTemplate(10);
        $enemyUnit = UnitFactory::createByTemplate(2);

        $command = CommandFactory::create([$unit, $deadUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new ResurrectionAction(
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_DEAD_ALLIES,
            50,
            'resurrection'
        );

        self::assertTrue($action->canByUsed());
        self::assertEquals(self::RESURRECTION_EN, $action->handle());
    }

    /**
     * Тест на формирование сообщения о воскрешении, на русском
     *
     *  @throws Exception
     */
    public function testMessageResurrectionRu(): void
    {
        $container = $this->getContainerWithRuLanguage();

        $unit = UnitFactory::createByTemplate(1, $container);
        $deadUnit = UnitFactory::createByTemplate(10, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);

        $command = CommandFactory::create([$unit, $deadUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new ResurrectionAction(
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_DEAD_ALLIES,
            50,
            'resurrected'
        );

        self::assertTrue($action->canByUsed());
        self::assertEquals(self::RESURRECTION_RU, $action->handle());
    }

    /**
     * Тест на формирование сообщения об уроне от эффекта, на английском
     *
     * @throws Exception
     */
    public function testMessageEffectDamageEn(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            10,
            'Poison',
            null,
            DamageAction::EFFECT_MESSAGE_METHOD
        );

        self::assertTrue($action->canByUsed());
        self::assertEquals(self::EFFECT_DAMAGE_EN, $action->handle());
    }

    /**
     * Тест на формирование сообщения об уроне от эффекта, на русском
     *
     * @throws Exception
     */
    public function testMessageEffectDamageRu(): void
    {
        $container = $this->getContainerWithRuLanguage();

        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2, $container);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            10,
            'Poison',
            null,
            DamageAction::EFFECT_MESSAGE_METHOD
        );

        self::assertTrue($action->canByUsed());
        self::assertEquals(self::EFFECT_DAMAGE_RU, $action->handle());
    }

    /**
     * Тест на формирование сообщения о лечении от эффекта, на английском
     *
     * @throws Exception
     */
    public function testMessageEffectHealEn(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(11, 2);

        $action = new HealAction(
            $unit,
            $enemyCommand,
            $command,
            HealAction::TARGET_SELF,
            15,
            'Healing Potion',
            HealAction::EFFECT_ANIMATION_METHOD,
            HealAction::EFFECT_MESSAGE_METHOD
        );

        self::assertTrue($action->canByUsed());
        self::assertEquals(self::EFFECT_HEAL_EN, $action->handle());
    }

    /**
     * Тест на формирование сообщения о лечении от эффекта, на русском
     *
     * @throws Exception
     */
    public function testMessageEffectHealRu(): void
    {
        $container = $this->getContainerWithRuLanguage();

        [$unit, $command, $enemyCommand] = BaseFactory::create(11, 2, $container);

        $action = new HealAction(
            $unit,
            $enemyCommand,
            $command,
            HealAction::TARGET_SELF,
            15,
            'Healing Potion',
            HealAction::EFFECT_ANIMATION_METHOD,
            HealAction::EFFECT_MESSAGE_METHOD
        );

        self::assertTrue($action->canByUsed());
        self::assertEquals(self::EFFECT_HEAL_RU, $action->handle());
    }

    /**
     * @throws Exception
     */
    public function testMessageUndefinedMethod(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $message = new Message();

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            30,
            'test attack',
            null,
            'undefinedMessageMethod'
        );

        $this->expectException(MessageException::class);
        $this->expectExceptionMessage(MessageException::UNDEFINED_MESSAGE_METHOD);
        $message->createMessage($action);
    }

    /**
     * @return ContainerInterface
     * @throws ContainerException
     */
    private function getContainerWithRuLanguage(): ContainerInterface
    {
        $translation = new Translation('ru');
        $message = new Message($translation);
        $container = new Container();
        $container->set(Message::class, $message);
        $container->set(Translation::class, $translation);
        return $container;
    }

    /**
     * Создает и возвращает EffectAction
     *
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @param int $typeTarget
     * @return ActionInterface
     * @throws Exception
     */
    private function getReserveForcesAction(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command,
        int $typeTarget
    ): ActionInterface
    {
        $actionFactory = new ActionFactory();

        $data = [
            'type'           => ActionInterface::EFFECT,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => $typeTarget,
            'name'           => 'use Reserve Forces',
            'effect'         => [
                'name'                  => 'Effect#123',
                'icon'                  => 'icon.png',
                'duration'              => 8,
                'on_apply_actions'      => [
                    [
                        'type'           => ActionInterface::BUFF,
                        'action_unit'    => $unit,
                        'enemy_command'  => $enemyCommand,
                        'allies_command' => $command,
                        'type_target'    => ActionInterface::TARGET_SELF,
                        'name'           => 'use Reserve Forces',
                        'modify_method'  => 'multiplierMaxLife',
                        'power'          => 130,
                    ],
                ],
                'on_next_round_actions' => [],
                'on_disable_actions'    => [],
            ],
        ];

        return $actionFactory->create($data);
    }
}
