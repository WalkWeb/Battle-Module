<?php

declare(strict_types=1);

namespace Tests\Battle\Result\Chat;

use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Action\BuffAction;
use Battle\Action\DamageAction;
use Battle\Action\EffectAction;
use Battle\Action\HealAction;
use Battle\Action\ParalysisAction;
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
    private const MESSAGE   = '<span style="color: #1e72e3">unit_1</span> attack <span style="color: #1e72e3">unit_2</span> on 20 damage';

    private const DAMAGE_EN = '<span style="color: #1e72e3">unit_1</span> attack <span style="color: #1e72e3">unit_2</span> on 20 damage';
    private const DAMAGE_RU = '<span style="color: #1e72e3">unit_1</span> атаковал <span style="color: #1e72e3">unit_2</span> на 20 урона';

    private const BLOCK_EN = '<span style="color: #1e72e3">unit_1</span> tried to strike, but <span style="color: #1e72e3">100_block</span> blocked it!';
    private const BLOCK_RU = '<span style="color: #1e72e3">unit_1</span> попытался нанести удар, но <span style="color: #1e72e3">100_block</span> заблокировал его!';

    private const DODGE_EN = '<span style="color: #1e72e3">unit_1</span> tried to strike, but <span style="color: #1e72e3">nimble_unit</span> dodged!';
    private const DODGE_RU = '<span style="color: #1e72e3">unit_1</span> попытался нанести удар, но <span style="color: #1e72e3">nimble_unit</span> уклонился!';

    private const DAMAGE_TWO_TARGET_EN = '<span style="color: #1e72e3">unit_1</span> attack <span style="color: #1e72e3">unit_2</span> and <span style="color: #1e72e3">unit_3</span> on 40 damage';
    private const DAMAGE_TWO_TARGET_RU = '<span style="color: #1e72e3">unit_1</span> атаковал <span style="color: #1e72e3">unit_2</span> и <span style="color: #1e72e3">unit_3</span> на 40 урона';

    private const DAMAGE_AND_BLOCK_EN = '<span style="color: #1e72e3">unit_1</span> attack <span style="color: #1e72e3">unit_2</span> on 20 damage. <span style="color: #1e72e3">unit_1</span> tried to strike, but <span style="color: #1e72e3">100_block</span> blocked it!';
    private const DAMAGE_AND_BLOCK_RU = '<span style="color: #1e72e3">unit_1</span> атаковал <span style="color: #1e72e3">unit_2</span> на 20 урона. <span style="color: #1e72e3">unit_1</span> попытался нанести удар, но <span style="color: #1e72e3">100_block</span> заблокировал его!';

    private const DAMAGE_AND_DODGE_EN = '<span style="color: #1e72e3">unit_1</span> attack <span style="color: #1e72e3">unit_2</span> on 20 damage. <span style="color: #1e72e3">unit_1</span> tried to strike, but <span style="color: #1e72e3">nimble_unit</span> dodged!';
    private const DAMAGE_AND_DODGE_RU = '<span style="color: #1e72e3">unit_1</span> атаковал <span style="color: #1e72e3">unit_2</span> на 20 урона. <span style="color: #1e72e3">unit_1</span> попытался нанести удар, но <span style="color: #1e72e3">nimble_unit</span> уклонился!';

    private const BLOCK_AND_DODGE_EN = '<span style="color: #1e72e3">unit_1</span> tried to strike, but <span style="color: #1e72e3">100_block</span> blocked it! <span style="color: #1e72e3">unit_1</span> tried to strike, but <span style="color: #1e72e3">nimble_unit</span> dodged!';
    private const BLOCK_AND_DODGE_RU = '<span style="color: #1e72e3">unit_1</span> попытался нанести удар, но <span style="color: #1e72e3">100_block</span> заблокировал его! <span style="color: #1e72e3">unit_1</span> попытался нанести удар, но <span style="color: #1e72e3">nimble_unit</span> уклонился!';

    private const DAMAGE_AND_BLOCK_AND_DODGE_EN = '<span style="color: #1e72e3">unit_1</span> attack <span style="color: #1e72e3">unit_2</span> on 20 damage. <span style="color: #1e72e3">unit_1</span> tried to strike, but <span style="color: #1e72e3">100_block</span> blocked it! <span style="color: #1e72e3">unit_1</span> tried to strike, but <span style="color: #1e72e3">nimble_unit</span> dodged!';
    private const DAMAGE_AND_BLOCK_AND_DODGE_RU = '<span style="color: #1e72e3">unit_1</span> атаковал <span style="color: #1e72e3">unit_2</span> на 20 урона. <span style="color: #1e72e3">unit_1</span> попытался нанести удар, но <span style="color: #1e72e3">100_block</span> заблокировал его! <span style="color: #1e72e3">unit_1</span> попытался нанести удар, но <span style="color: #1e72e3">nimble_unit</span> уклонился!';

    private const DAMAGE_THREE_TARGET_EN = '<span style="color: #1e72e3">unit_1</span> attack <span style="color: #1e72e3">unit_2</span>, <span style="color: #1e72e3">unit_3</span> and <span style="color: #1e72e3">unit_4</span> on 60 damage';
    private const DAMAGE_THREE_TARGET_RU = '<span style="color: #1e72e3">unit_1</span> атаковал <span style="color: #1e72e3">unit_2</span>, <span style="color: #1e72e3">unit_3</span> и <span style="color: #1e72e3">unit_4</span> на 60 урона';

    private const DAMAGE_ABILITY_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> at <span style="color: #1e72e3">unit_2</span> on 50 damage';
    private const DAMAGE_ABILITY_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> по <span style="color: #1e72e3">unit_2</span> на 50 урона';

    private const DAMAGE_ABILITY_BLOCK_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> but <span style="color: #1e72e3">100_block</span> blocked it!';
    private const DAMAGE_ABILITY_BLOCK_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> но <span style="color: #1e72e3">100_block</span> заблокировал его!';

    private const DAMAGE_ABILITY_DODGE_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> but <span style="color: #1e72e3">nimble_unit</span> dodged!';
    private const DAMAGE_ABILITY_DODGE_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> но <span style="color: #1e72e3">nimble_unit</span> уклонился!';

    private const ABILITY_DAMAGE_AND_BLOCK_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> at <span style="color: #1e72e3">unit_2</span> on 50 damage. <span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> but <span style="color: #1e72e3">100_block</span> blocked it!';
    private const ABILITY_DAMAGE_AND_BLOCK_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> по <span style="color: #1e72e3">unit_2</span> на 50 урона. <span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> но <span style="color: #1e72e3">100_block</span> заблокировал его!';

    private const ABILITY_DAMAGE_AND_DODGE_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> at <span style="color: #1e72e3">unit_2</span> on 50 damage. <span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> but <span style="color: #1e72e3">nimble_unit</span> dodged!';
    private const ABILITY_DAMAGE_AND_DODGE_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> по <span style="color: #1e72e3">unit_2</span> на 50 урона. <span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> но <span style="color: #1e72e3">nimble_unit</span> уклонился!';

    private const ABILITY_BLOCK_AND_DODGE_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> but <span style="color: #1e72e3">100_block</span> blocked it! <span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> but <span style="color: #1e72e3">nimble_unit</span> dodged!';
    private const ABILITY_BLOCK_AND_DODGE_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> но <span style="color: #1e72e3">100_block</span> заблокировал его! <span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> но <span style="color: #1e72e3">nimble_unit</span> уклонился!';

    private const ABILITY_DAMAGE_AND_BLOCK_AND_DODGE_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> at <span style="color: #1e72e3">unit_2</span> on 50 damage. <span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> but <span style="color: #1e72e3">100_block</span> blocked it! <span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> but <span style="color: #1e72e3">nimble_unit</span> dodged!';
    private const ABILITY_DAMAGE_AND_BLOCK_AND_DODGE_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> по <span style="color: #1e72e3">unit_2</span> на 50 урона. <span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> но <span style="color: #1e72e3">100_block</span> заблокировал его! <span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> но <span style="color: #1e72e3">nimble_unit</span> уклонился!';

    private const HEAL_EN = '<span style="color: #1e72e3">unit_1</span> heal <span style="color: #1e72e3">wounded_unit</span> on 20 life';
    private const HEAL_RU = '<span style="color: #1e72e3">unit_1</span> вылечил <span style="color: #1e72e3">wounded_unit</span> на 20 здоровья';

    private const SUMMON_EN = '<span style="color: #1e72e3">unit_1</span> summon <img src="/images/icons/ability/275.png" alt="" /> <span class="ability">Imp</span>';
    private const SUMMON_RU = '<span style="color: #1e72e3">unit_1</span> призвал <img src="/images/icons/ability/275.png" alt="" /> <span class="ability">Беса</span>';

    private const WAIT_EN = '<span style="color: #1e72e3">unit_1</span> preparing to attack';
    private const WAIT_RU = '<span style="color: #1e72e3">unit_1</span> готовится к атаке';

    private const PARALYSIS_EN = '<span style="color: #1e72e3">unit_1</span> paralyzed and unable to move';
    private const PARALYSIS_RU = '<span style="color: #1e72e3">unit_1</span> парализован и не может двигаться';

    private const EFFECT_DAMAGE_EN = '<span style="color: #1e72e3">unit_1</span> received damage on 10 life from effect <span class="ability">Poison</span>';
    private const EFFECT_DAMAGE_RU = '<span style="color: #1e72e3">unit_1</span> получил урон на 10 здоровья от эффекта <span class="ability">Отравление</span>';

    // В текущих способностях сообщение от BuffAction не формируется, оно формируется через EffectAction
    // По этому это сообщение выглядит кривовато, но это нормально
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
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            $unit->getOffense()->getDamage(),
            true
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::MESSAGE, $this->getChat()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения об уроне
     *
     * @throws Exception
     */
    public function testChatAddMessageDamage(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            $unit->getOffense()->getDamage(),
            true
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::DAMAGE_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::DAMAGE_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения о блоке
     *
     * @throws Exception
     */
    public function testChatAddMessageBlockedDamage(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 28);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            $unit->getOffense()->getDamage(),
            true
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::BLOCK_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::BLOCK_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения об уклонении
     *
     * @throws Exception
     */
    public function testChatAddMessageDodgedDamage(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 30);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            $unit->getOffense()->getDamage(),
            true
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::DODGE_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::DODGE_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения об уроне со способности
     *
     * @throws Exception
     */
    public function testChatAddMessageDamageAbility(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            50,
            true,
            HeavyStrikeAbility::NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            HeavyStrikeAbility::MESSAGE_METHOD,
            HeavyStrikeAbility::ICON
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::DAMAGE_ABILITY_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::DAMAGE_ABILITY_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения о блоке способности
     *
     * @throws Exception
     */
    public function testChatAddMessageBlockedDamageAbility(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 28);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            50,
            true,
            HeavyStrikeAbility::NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            HeavyStrikeAbility::MESSAGE_METHOD,
            HeavyStrikeAbility::ICON
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::DAMAGE_ABILITY_BLOCK_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::DAMAGE_ABILITY_BLOCK_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения об уклонении от способности
     *
     * @throws Exception
     */
    public function testChatAddMessageDodgedDamageAbility(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 30);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            50,
            true,
            HeavyStrikeAbility::NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            HeavyStrikeAbility::MESSAGE_METHOD,
            HeavyStrikeAbility::ICON
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::DAMAGE_ABILITY_DODGE_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::DAMAGE_ABILITY_DODGE_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения о лечении
     *
     * @throws Exception
     */
    public function testChatAddMessageHeal(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $otherUnit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit, $otherUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new HealAction(
            $unit,
            $enemyCommand,
            $command,
            HealAction::TARGET_WOUNDED_ALLIES,
            $unit->getOffense()->getDamage()
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::HEAL_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::HEAL_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения о призыве
     *
     * @throws Exception
     */
    public function testChatAddMessageSummon(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);
        $summon = UnitFactory::createByTemplate(18);
        $icon = '/images/icons/ability/275.png';

        $action = new SummonAction(
            $unit,
            $enemyCommand,
            $command,
            'Imp',
            $summon,
            $icon
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::SUMMON_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::SUMMON_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения о пропуске хода
     *
     * @throws Exception
     */
    public function testChatAddMessageWait(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $action = new WaitAction($unit, $enemyCommand, $command);

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::WAIT_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::WAIT_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщение о параличе (пропуске хода аналогично wait)
     *
     * @throws Exception
     */
    public function testChatAddMessageParalysis(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $action = new ParalysisAction($unit, $enemyCommand, $command);

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::PARALYSIS_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::PARALYSIS_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения о применении эффекта на себя
     *
     * @throws Exception
     */
    public function testChatAddMessageApplyEffectToSelf(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $action = $this->getReserveForcesAction($unit, $enemyCommand, $command, EffectAction::TARGET_SELF);

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::APPLY_EFFECT_SELF_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::APPLY_EFFECT_SELF_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения о применении эффекта на другого юнита
     *
     * @throws Exception
     */
    public function testChatAddMessageApplyEffectToOther(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $action = $this->getReserveForcesAction($unit, $enemyCommand, $command, EffectAction::TARGET_RANDOM_ENEMY);

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::APPLY_EFFECT_TO_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::APPLY_EFFECT_TO_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения об усилении
     *
     * @throws Exception
     */
    public function testChatAddMessageBuff(): void
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

        $action->handle();

        self::assertEquals(self::BUFF_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::BUFF_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения о воскрешении
     *
     *  @throws Exception
     */
    public function testChatAddMessageResurrection(): void
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

        $action->handle();

        self::assertEquals(self::RESURRECTION_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::RESURRECTION_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения об уроне от эффекта
     *
     * @throws Exception
     */
    public function testChatAddMessageEffectDamage(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            10,
            true,
            'Poison',
            null,
            DamageAction::EFFECT_MESSAGE_METHOD
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::EFFECT_DAMAGE_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::EFFECT_DAMAGE_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения о лечении от эффекта
     *
     * @throws Exception
     */
    public function testChatAddMessageEffectHeal(): void
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

        $action->handle();

        self::assertEquals(self::EFFECT_HEAL_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::EFFECT_HEAL_RU, $this->getChatRu()->addMessage($action));
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

        $action->handle();

        self::assertEquals(self::SKIP_MESSAGE, $this->getChat()->addMessage($action));
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
            true,
            'test attack',
            null,
            'undefinedMessageMethod'
        );

        $this->expectException(ChatException::class);
        $this->expectExceptionMessage(ChatException::UNDEFINED_MESSAGE_METHOD);
        $chat->addMessage($action);
    }

    /**
     * Тест на формирование сообщения удара по двум целям одновременно
     *
     * @throws Exception
     */
    public function testChatAddMessageTwoTarget(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $firstEnemyUnit = UnitFactory::createByTemplate(2);
        $secondaryEnemyUnit = UnitFactory::createByTemplate(3);

        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$firstEnemyUnit, $secondaryEnemyUnit]);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_ALL_ENEMY,
            $unit->getOffense()->getDamage(),
            true
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::DAMAGE_TWO_TARGET_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::DAMAGE_TWO_TARGET_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения, когда одна цель получила урон, а другая заблокировала
     *
     * @throws Exception
     */
    public function testChatAddMessageDamageAndBlocked(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $firstEnemyUnit = UnitFactory::createByTemplate(2);
        $secondaryEnemyUnit = UnitFactory::createByTemplate(28);

        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$firstEnemyUnit, $secondaryEnemyUnit]);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_ALL_ENEMY,
            $unit->getOffense()->getDamage(),
            true
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::DAMAGE_AND_BLOCK_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::DAMAGE_AND_BLOCK_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения, когда одна цель получила урон, а другая уклонилась
     *
     * @throws Exception
     */
    public function testChatAddMessageDamageAndDodged(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $firstEnemyUnit = UnitFactory::createByTemplate(2);
        $secondaryEnemyUnit = UnitFactory::createByTemplate(30);

        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$firstEnemyUnit, $secondaryEnemyUnit]);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_ALL_ENEMY,
            $unit->getOffense()->getDamage(),
            true
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::DAMAGE_AND_DODGE_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::DAMAGE_AND_DODGE_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения, когда одна цель заблокировала урон, а другая уклонилась
     *
     * @throws Exception
     */
    public function testChatAddMessageBlockedAndDodged(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $firstEnemyUnit = UnitFactory::createByTemplate(30);
        $secondaryEnemyUnit = UnitFactory::createByTemplate(28);

        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$firstEnemyUnit, $secondaryEnemyUnit]);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_ALL_ENEMY,
            $unit->getOffense()->getDamage(),
            true
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::BLOCK_AND_DODGE_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::BLOCK_AND_DODGE_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения, когда одна цель получила урон, другая заблокировала урон, а третья уклонилась
     *
     * @throws Exception
     */
    public function testChatAddMessageDamageAndBlockedAndDodged(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $blockedUnit = UnitFactory::createByTemplate(28);
        $dodgedUnit = UnitFactory::createByTemplate(30);

        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit, $blockedUnit, $dodgedUnit]);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_ALL_ENEMY,
            $unit->getOffense()->getDamage(),
            true
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::DAMAGE_AND_BLOCK_AND_DODGE_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::DAMAGE_AND_BLOCK_AND_DODGE_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения, когда одна цель получила урон, а другая заблокировала способность
     *
     * @throws Exception
     */
    public function testChatAddMessageAbilityDamageAndBlocked(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $firstEnemyUnit = UnitFactory::createByTemplate(2);
        $secondaryEnemyUnit = UnitFactory::createByTemplate(28);

        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$firstEnemyUnit, $secondaryEnemyUnit]);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_ALL_ENEMY,
            50,
            true,
            HeavyStrikeAbility::NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            HeavyStrikeAbility::MESSAGE_METHOD,
            HeavyStrikeAbility::ICON
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::ABILITY_DAMAGE_AND_BLOCK_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::ABILITY_DAMAGE_AND_BLOCK_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения, когда одна цель получила урон, а другая уклонилась от способности
     *
     * @throws Exception
     */
    public function testChatAddMessageAbilityDamageAndDodged(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $dodgeUnit = UnitFactory::createByTemplate(30);

        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit, $dodgeUnit]);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_ALL_ENEMY,
            50,
            true,
            HeavyStrikeAbility::NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            HeavyStrikeAbility::MESSAGE_METHOD,
            HeavyStrikeAbility::ICON
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::ABILITY_DAMAGE_AND_DODGE_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::ABILITY_DAMAGE_AND_DODGE_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения, когда одна цель заблокировала урон, а другая уклонилась от способности
     *
     * @throws Exception
     */
    public function testChatAddMessageAbilityBlockedAndDodged(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $blockedUnit = UnitFactory::createByTemplate(28);
        $dodgedUnit = UnitFactory::createByTemplate(30);

        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$blockedUnit, $dodgedUnit]);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_ALL_ENEMY,
            50,
            true,
            HeavyStrikeAbility::NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            HeavyStrikeAbility::MESSAGE_METHOD,
            HeavyStrikeAbility::ICON
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::ABILITY_BLOCK_AND_DODGE_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::ABILITY_BLOCK_AND_DODGE_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения, когда одна цель получила урон, а другая заблокировала от способности
     *
     * @throws Exception
     */
    public function testChatAddMessageAbilityDamageAndBlockedAndDodged(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $blockedUnit = UnitFactory::createByTemplate(28);
        $dodgedUnit = UnitFactory::createByTemplate(30);

        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit, $blockedUnit, $dodgedUnit]);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_ALL_ENEMY,
            50,
            true,
            HeavyStrikeAbility::NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            HeavyStrikeAbility::MESSAGE_METHOD,
            HeavyStrikeAbility::ICON
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::ABILITY_DAMAGE_AND_BLOCK_AND_DODGE_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::ABILITY_DAMAGE_AND_BLOCK_AND_DODGE_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения удара по трем целям одновременно
     *
     * @throws Exception
     */
    public function testChatAddMessageThreeTarget(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $firstEnemyUnit = UnitFactory::createByTemplate(2);
        $secondaryEnemyUnit = UnitFactory::createByTemplate(3);
        $thirdEnemyUnit = UnitFactory::createByTemplate(4);

        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$firstEnemyUnit, $secondaryEnemyUnit, $thirdEnemyUnit]);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_ALL_ENEMY,
            $unit->getOffense()->getDamage(),
            true
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::DAMAGE_THREE_TARGET_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::DAMAGE_THREE_TARGET_RU, $this->getChatRu()->addMessage($action));
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
