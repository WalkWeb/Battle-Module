<?php

declare(strict_types=1);

namespace Tests\Battle\Result\Chat;

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
use Battle\Result\Chat\Chat;
use Battle\Result\Chat\ChatException;
use Battle\Unit\Ability\Damage\HeavyStrikeAbility;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\BaseFactory;
use Tests\Battle\Factory\UnitFactory;

class ChatTest extends AbstractUnitTest
{
    private const DAMAGE_EN = '<span style="color: #1e72e3">unit_1</span> attack <span style="color: #1e72e3">unit_2</span> on 20 damage';
    private const DAMAGE_RU = '<span style="color: #1e72e3">unit_1</span> атаковал <span style="color: #1e72e3">unit_2</span> на 20 урона';

    private const DAMAGE_ABILITY_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> at <span style="color: #1e72e3">unit_2</span> on 50 damage';
    private const DAMAGE_ABILITY_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> по <span style="color: #1e72e3">unit_2</span> на 50 урона';

    private const HEAL_EN = '<span style="color: #1e72e3">unit_1</span> heal <span style="color: #1e72e3">wounded_unit</span> on 20 life';
    private const HEAL_RU = '<span style="color: #1e72e3">unit_1</span> вылечил <span style="color: #1e72e3">wounded_unit</span> на 20 здоровья';

    // TODO Почему нет иконки?
    private const SUMMON_EN = '<span style="color: #1e72e3">unit_1</span> summon <span class="ability">Imp</span>';
    private const SUMMON_RU = '<span style="color: #1e72e3">unit_1</span> призвал <span class="ability">Беса</span>';

    private const WAIT_EN = '<span style="color: #1e72e3">unit_1</span> preparing to attack';
    private const WAIT_RU = '<span style="color: #1e72e3">unit_1</span> готовится к атаке';

    private const EFFECT_DAMAGE_EN = '<span style="color: #1e72e3">unit_1</span> received damage on 10 life from effect <span class="ability">Poison</span>';
    private const EFFECT_DAMAGE_RU = '<span style="color: #1e72e3">unit_1</span> получил урон на 10 здоровья от эффекта <span class="ability">Отравление</span>';

    // TODO В текущих способностях сообщение от BuffAction не формируется, оно формируется через EffectAction
    // TODO По этому текущие сообщения выглядят кривовато
    private const BUFF_EN = '<span style="color: #1e72e3">unit_1</span> Reserve Forces';
    private const BUFF_RU = '<span style="color: #1e72e3">unit_1</span> Резервные Силы';

    // Сейчас сообщения выглядят некорректно, т.к. сообщение о воскрешении подразумевает, что воскрешение использовано со способности
    private const RESURRECTION_EN = '<span style="color: #1e72e3">unit_1</span> use <span class="ability">ExampleActionName</span> and resurrected <span style="color: #1e72e3">dead_unit</span>';
    private const RESURRECTION_RU = '<span style="color: #1e72e3">unit_1</span> использовал <span class="ability">ExampleActionName</span> и воскресил <span style="color: #1e72e3">dead_unit</span>';

    private const EFFECT_HEAL_EN = '<span style="color: #1e72e3">wounded_unit</span> restored 15 life from effect <span class="ability">Healing Potion</span>';
    private const EFFECT_HEAL_RU = '<span style="color: #1e72e3">wounded_unit</span> восстановил 15 здоровья от эффекта <span class="ability">Лечебное зелье</span>';

    private const APPLY_EFFECT_SELF_EN = '<span style="color: #1e72e3">unit_1</span> use <span class="ability">Reserve Forces</span>';
    private const APPLY_EFFECT_SELF_RU = '<span style="color: #1e72e3">unit_1</span> использовал <span class="ability">Резервные Силы</span>';

    private const APPLY_EFFECT_TO_EN = '<span style="color: #1e72e3">unit_1</span> use <span class="ability">Reserve Forces</span> on <span style="color: #1e72e3">unit_2</span>';
    private const APPLY_EFFECT_TO_RU = '<span style="color: #1e72e3">unit_1</span> использовал <span class="ability">Резервные Силы</span> на <span style="color: #1e72e3">unit_2</span>';

    private const SKIP_MESSAGE = '';

    /**
     * Тест на проверку того, что создаваемое сообщение также сохраняется в самом чате
     *
     * @throws Exception
     */
    public function testChatAddedMessage(): void
    {
        $container = $this->getContainerWithRuLanguage();

        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2, $container);

        $action = new DamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        $message = $action->handle();

        $chat = $container->getChat();

        self::assertEquals([$message], $chat->getMessages());
    }

    /**
     * Тест на формирование сообщения об уроне, на английском
     *
     * @throws Exception
     */
    public function testChatAddMessageDamageEn(): void
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
    public function testChatAddMessageDamageRu(): void
    {
        $container = $this->getContainerWithRuLanguage();

        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2, $container);

        $action = new DamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        self::assertTrue($action->canByUsed());
        self::assertEquals(self::DAMAGE_RU, $action->handle());
    }

    /**
     * Тест на формирование сообщения об уроне со способности, на английском
     *
     * @throws Exception
     */
    public function testChatAddMessageDamageAbilityEn(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            50,
            HeavyStrikeAbility::NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            HeavyStrikeAbility::MESSAGE_METHOD,
            HeavyStrikeAbility::ICON
        );

        self::assertTrue($action->canByUsed());
        self::assertEquals(self::DAMAGE_ABILITY_EN, $action->handle());
    }

    /**
     * Тест на формирование сообщения об уроне со способности, на русском
     *
     * @throws Exception
     */
    public function testChatAddMessageDamageAbilityRu(): void
    {
        $container = $this->getContainerWithRuLanguage();

        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2, $container);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            50,
            HeavyStrikeAbility::NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            HeavyStrikeAbility::MESSAGE_METHOD,
            HeavyStrikeAbility::ICON
        );

        self::assertTrue($action->canByUsed());
        self::assertEquals(self::DAMAGE_ABILITY_RU, $action->handle());
    }

    /**
     * Тест на формирование сообщения о лечении, на английском
     *
     * @throws Exception
     */
    public function testChatAddMessageHealEn(): void
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
    public function testChatAddMessageHealRu(): void
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
    public function testChatAddMessageSummonEn(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);
        $summon = UnitFactory::createByTemplate(18);

        $action = new SummonAction(
            $unit,
            $enemyCommand,
            $command,
            'Imp',
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
    public function testChatAddMessageSummonRu(): void
    {
        $container = $this->getContainerWithRuLanguage();

        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2, $container);
        $summon = UnitFactory::createByTemplate(18);

        $action = new SummonAction(
            $unit,
            $enemyCommand,
            $command,
            'Imp',
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
    public function testChatAddMessageWaitEn(): void
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
    public function testChatAddMessageWaitRu(): void
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
    public function testChatAddMessageApplyEffectToSelfEn(): void
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
    public function testChatAddMessageApplyEffectToSelfRu(): void
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
    public function testChatAddMessageApplyEffectToOtherEn(): void
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
    public function testChatAddMessageApplyEffectToOtherRu(): void
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
    public function testChatAddMessageBuffEn(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $action = new BuffAction(
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'Reserve Forces',
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
    public function testChatAddMessageBuffRu(): void
    {
        $container = $this->getContainerWithRuLanguage();

        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2, $container);

        $action = new BuffAction(
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'Reserve Forces',
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
    public function testChatAddMessageResurrectionEn(): void
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
            'ExampleActionName'
        );

        self::assertTrue($action->canByUsed());
        self::assertEquals(self::RESURRECTION_EN, $action->handle());
    }

    /**
     * Тест на формирование сообщения о воскрешении, на русском
     *
     *  @throws Exception
     */
    public function testChatAddMessageResurrectionRu(): void
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
            'ExampleActionName'
        );

        self::assertTrue($action->canByUsed());
        self::assertEquals(self::RESURRECTION_RU, $action->handle());
    }

    /**
     * Тест на формирование сообщения об уроне от эффекта, на английском
     *
     * @throws Exception
     */
    public function testChatAddMessageEffectDamageEn(): void
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
    public function testChatAddMessageEffectDamageRu(): void
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
    public function testChatAddMessageEffectHealEn(): void
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
    public function testChatAddMessageEffectHealRu(): void
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
     * Тест на пропуск формирования сообщения для чата - будет возвращена пустая строка, а в сам чат (массив сообщений)
     * ничего не будет добавлено
     *
     * @throws Exception
     */
    public function testChatSkipMessage(): void
    {
        $container = new Container();
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2, $container);

        $action = new BuffAction(
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'test name',
            'multiplierMaxLife',
            200,
            BuffAction::SKIP_MESSAGE_METHOD
        );

        self::assertTrue($action->canByUsed());
        self::assertEquals(self::SKIP_MESSAGE, $action->handle());
        self::assertEquals([], $container->getChat()->getMessages());
    }

    /**
     * @throws Exception
     */
    public function testChatAddMessageUndefinedMethod(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $chat = new Chat();

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

        $this->expectException(ChatException::class);
        $this->expectExceptionMessage(ChatException::UNDEFINED_MESSAGE_METHOD);
        $chat->addMessage($action);
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
            'name'           => 'Reserve Forces',
            'use_message'    => 'use',
            'message_method' => 'applyEffect',
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
