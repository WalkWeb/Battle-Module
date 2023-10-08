<?php

declare(strict_types=1);

namespace Tests\Battle\Response\Chat;

use Battle\Action\ActionInterface;
use Battle\Action\BuffAction;
use Battle\Action\DamageAction;
use Battle\Action\EffectAction;
use Battle\Action\HealAction;
use Battle\Action\ManaRestoreAction;
use Battle\Action\ParalysisAction;
use Battle\Action\ResurrectionAction;
use Battle\Action\SummonAction;
use Battle\Action\WaitAction;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Response\Chat\Chat;
use Battle\Response\Chat\ChatException;
use Battle\Unit\Offense\OffenseFactory;
use Battle\Unit\UnitInterface;
use Battle\Weapon\Type\WeaponTypeInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\BaseFactory;
use Tests\Factory\UnitFactory;

class ChatTest extends AbstractUnitTest
{
    private const DAMAGE_EN = '<span style="color: #1e72e3">unit_1</span> hit for 20 damage against <span style="color: #1e72e3">unit_2</span>';
    private const DAMAGE_RU = '<span style="color: #1e72e3">unit_1</span> нанес удар на 20 урона по <span style="color: #1e72e3">unit_2</span>';

    private const DAMAGE_AND_LIFE_VAMPIRISM_EN = '<span style="color: #1e72e3">unit_vampire</span> hit for 50 damage against <span style="color: #1e72e3">unit_2</span> and restore 25 life';
    private const DAMAGE_AND_LIFE_VAMPIRISM_RU = '<span style="color: #1e72e3">unit_vampire</span> нанес удар на 50 урона по <span style="color: #1e72e3">unit_2</span> и восстановил 25 здоровья';

    private const DAMAGE_AND_MANA_VAMPIRISM_EN = '<span style="color: #1e72e3">unit_2</span> hit for 30 damage against <span style="color: #1e72e3">unit_5</span> and restore 3 mana';
    private const DAMAGE_AND_MANA_VAMPIRISM_RU = '<span style="color: #1e72e3">unit_2</span> нанес удар на 30 урона по <span style="color: #1e72e3">unit_5</span> и восстановил 3 маны';

    private const DAMAGE_AND_LIFE_AND_MANA_VAMPIRISM_EN = '<span style="color: #1e72e3">unit_1</span> hit for 20 damage against <span style="color: #1e72e3">unit_5</span> and restore 4 life and 4 mana';
    private const DAMAGE_AND_LIFE_AND_MANA_VAMPIRISM_RU = '<span style="color: #1e72e3">unit_1</span> нанес удар на 20 урона по <span style="color: #1e72e3">unit_5</span> и восстановил 4 здоровья и 4 маны';

    private const CRITICAL_DAMAGE_EN = '<span style="color: #1e72e3">unit_1</span> critical hit for 40 damage against <span style="color: #1e72e3">unit_2</span>';
    private const CRITICAL_DAMAGE_RU = '<span style="color: #1e72e3">unit_1</span> нанес критический удар на 40 урона по <span style="color: #1e72e3">unit_2</span>';

    private const BLOCK_EN = '<span style="color: #1e72e3">unit_1</span> tried to strike, but <span style="color: #1e72e3">100_block</span> blocked it!';
    private const BLOCK_RU = '<span style="color: #1e72e3">unit_1</span> попытался нанести удар, но <span style="color: #1e72e3">100_block</span> заблокировал его!';

    private const DODGE_EN = '<span style="color: #1e72e3">unit_1</span> tried to strike, but <span style="color: #1e72e3">nimble_unit</span> dodged!';
    private const DODGE_RU = '<span style="color: #1e72e3">unit_1</span> попытался нанести удар, но <span style="color: #1e72e3">nimble_unit</span> уклонился!';

    private const DAMAGE_TWO_TARGET_EN = '<span style="color: #1e72e3">unit_1</span> hit for 40 damage against <span style="color: #1e72e3">unit_2</span> and <span style="color: #1e72e3">unit_3</span>';
    private const DAMAGE_TWO_TARGET_RU = '<span style="color: #1e72e3">unit_1</span> нанес удар на 40 урона по <span style="color: #1e72e3">unit_2</span> и <span style="color: #1e72e3">unit_3</span>';

    private const DAMAGE_AND_BLOCK_EN = '<span style="color: #1e72e3">unit_1</span> hit for 20 damage against <span style="color: #1e72e3">unit_2</span>. <span style="color: #1e72e3">unit_1</span> tried to strike, but <span style="color: #1e72e3">100_block</span> blocked it!';
    private const DAMAGE_AND_BLOCK_RU = '<span style="color: #1e72e3">unit_1</span> нанес удар на 20 урона по <span style="color: #1e72e3">unit_2</span>. <span style="color: #1e72e3">unit_1</span> попытался нанести удар, но <span style="color: #1e72e3">100_block</span> заблокировал его!';

    private const DAMAGE_AND_DODGE_EN = '<span style="color: #1e72e3">unit_1</span> hit for 20 damage against <span style="color: #1e72e3">unit_2</span>. <span style="color: #1e72e3">unit_1</span> tried to strike, but <span style="color: #1e72e3">nimble_unit</span> dodged!';
    private const DAMAGE_AND_DODGE_RU = '<span style="color: #1e72e3">unit_1</span> нанес удар на 20 урона по <span style="color: #1e72e3">unit_2</span>. <span style="color: #1e72e3">unit_1</span> попытался нанести удар, но <span style="color: #1e72e3">nimble_unit</span> уклонился!';

    private const BLOCK_AND_DODGE_EN = '<span style="color: #1e72e3">unit_1</span> tried to strike, but <span style="color: #1e72e3">100_block</span> blocked it! <span style="color: #1e72e3">unit_1</span> tried to strike, but <span style="color: #1e72e3">nimble_unit</span> dodged!';
    private const BLOCK_AND_DODGE_RU = '<span style="color: #1e72e3">unit_1</span> попытался нанести удар, но <span style="color: #1e72e3">100_block</span> заблокировал его! <span style="color: #1e72e3">unit_1</span> попытался нанести удар, но <span style="color: #1e72e3">nimble_unit</span> уклонился!';

    private const DAMAGE_AND_BLOCK_AND_DODGE_EN = '<span style="color: #1e72e3">unit_1</span> hit for 20 damage against <span style="color: #1e72e3">unit_2</span>. <span style="color: #1e72e3">unit_1</span> tried to strike, but <span style="color: #1e72e3">100_block</span> blocked it! <span style="color: #1e72e3">unit_1</span> tried to strike, but <span style="color: #1e72e3">nimble_unit</span> dodged!';
    private const DAMAGE_AND_BLOCK_AND_DODGE_RU = '<span style="color: #1e72e3">unit_1</span> нанес удар на 20 урона по <span style="color: #1e72e3">unit_2</span>. <span style="color: #1e72e3">unit_1</span> попытался нанести удар, но <span style="color: #1e72e3">100_block</span> заблокировал его! <span style="color: #1e72e3">unit_1</span> попытался нанести удар, но <span style="color: #1e72e3">nimble_unit</span> уклонился!';

    private const DAMAGE_THREE_TARGET_EN = '<span style="color: #1e72e3">unit_1</span> hit for 60 damage against <span style="color: #1e72e3">unit_2</span>, <span style="color: #1e72e3">unit_3</span> and <span style="color: #1e72e3">unit_4</span>';
    private const DAMAGE_THREE_TARGET_RU = '<span style="color: #1e72e3">unit_1</span> нанес удар на 60 урона по <span style="color: #1e72e3">unit_2</span>, <span style="color: #1e72e3">unit_3</span> и <span style="color: #1e72e3">unit_4</span>';

    private const DAMAGE_ABILITY_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> and hit for 50 damage against <span style="color: #1e72e3">unit_2</span>';
    private const DAMAGE_ABILITY_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> и нанес удар на 50 урона по <span style="color: #1e72e3">unit_2</span>';

    private const DAMAGE_ABILITY_AND_VAMPIRISM_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> and hit for 50 damage against <span style="color: #1e72e3">unit_2</span> and restore 25 life';
    private const DAMAGE_ABILITY_AND_VAMPIRISM_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> и нанес удар на 50 урона по <span style="color: #1e72e3">unit_2</span> и восстановил 25 здоровья';

    private const CRITICAL_DAMAGE_ABILITY_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> and critical hit for 100 damage against <span style="color: #1e72e3">unit_2</span>';
    private const CRITICAL_DAMAGE_ABILITY_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> и нанес критический удар на 100 урона по <span style="color: #1e72e3">unit_2</span>';

    private const DAMAGE_ABILITY_BLOCK_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> but <span style="color: #1e72e3">100_block</span> blocked it!';
    private const DAMAGE_ABILITY_BLOCK_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> но <span style="color: #1e72e3">100_block</span> заблокировал его!';

    private const DAMAGE_ABILITY_DODGE_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> but <span style="color: #1e72e3">nimble_unit</span> dodged!';
    private const DAMAGE_ABILITY_DODGE_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> но <span style="color: #1e72e3">nimble_unit</span> уклонился!';

    private const ABILITY_DAMAGE_AND_BLOCK_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> and hit for 50 damage against <span style="color: #1e72e3">unit_2</span>. <span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> but <span style="color: #1e72e3">100_block</span> blocked it!';
    private const ABILITY_DAMAGE_AND_BLOCK_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> и нанес удар на 50 урона по <span style="color: #1e72e3">unit_2</span>. <span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> но <span style="color: #1e72e3">100_block</span> заблокировал его!';

    private const ABILITY_DAMAGE_AND_DODGE_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> and hit for 50 damage against <span style="color: #1e72e3">unit_2</span>. <span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> but <span style="color: #1e72e3">nimble_unit</span> dodged!';
    private const ABILITY_DAMAGE_AND_DODGE_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> и нанес удар на 50 урона по <span style="color: #1e72e3">unit_2</span>. <span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> но <span style="color: #1e72e3">nimble_unit</span> уклонился!';

    private const ABILITY_BLOCK_AND_DODGE_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> but <span style="color: #1e72e3">100_block</span> blocked it! <span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> but <span style="color: #1e72e3">nimble_unit</span> dodged!';
    private const ABILITY_BLOCK_AND_DODGE_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> но <span style="color: #1e72e3">100_block</span> заблокировал его! <span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> но <span style="color: #1e72e3">nimble_unit</span> уклонился!';

    private const ABILITY_DAMAGE_AND_BLOCK_AND_DODGE_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> and hit for 50 damage against <span style="color: #1e72e3">unit_2</span>. <span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> but <span style="color: #1e72e3">100_block</span> blocked it! <span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> but <span style="color: #1e72e3">nimble_unit</span> dodged!';
    private const ABILITY_DAMAGE_AND_BLOCK_AND_DODGE_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> и нанес удар на 50 урона по <span style="color: #1e72e3">unit_2</span>. <span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> но <span style="color: #1e72e3">100_block</span> заблокировал его! <span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Тяжелый Удар</span> но <span style="color: #1e72e3">nimble_unit</span> уклонился!';

    private const HEAL_EN = '<span style="color: #1e72e3">unit_1</span> heal <span style="color: #1e72e3">wounded_unit</span> on 20 life';
    private const HEAL_RU = '<span style="color: #1e72e3">unit_1</span> вылечил <span style="color: #1e72e3">wounded_unit</span> на 20 здоровья';

    private const HEAL_ABILITY_OTHER_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/196.png" alt="" /> <span class="ability">Great Heal</span> and heal <span style="color: #1e72e3">wounded_unit</span> on 60 life';
    private const HEAL_ABILITY_OTHER_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/196.png" alt="" /> <span class="ability">Сильное Лечение</span> и вылечил <span style="color: #1e72e3">wounded_unit</span> на 60 здоровья';

    private const HEAL_ABILITY_SELF_EN = '<span style="color: #1e72e3">wounded_unit</span> use <img src="/images/icons/ability/196.png" alt="" /> <span class="ability">Great Heal</span> and healed itself on 60 life';
    private const HEAL_ABILITY_SELF_RU = '<span style="color: #1e72e3">wounded_unit</span> использовал <img src="/images/icons/ability/196.png" alt="" /> <span class="ability">Сильное Лечение</span> и вылечил себя на 60 здоровья';

    private const MANA_RESTORE_EN = '<span style="color: #1e72e3">wounded_unit</span> restore <span style="color: #1e72e3">wounded_unit</span> 20 mana';
    private const MANA_RESTORE_RU = '<span style="color: #1e72e3">wounded_unit</span> восстановил <span style="color: #1e72e3">wounded_unit</span> 20 маны';

    private const MANA_RESTORE_ABILITY_EN = '<span style="color: #1e72e3">wounded_unit</span> use <img src="/images/icons/ability/234.png" alt="" /> <span class="ability">Restore Potion</span> and restore <span style="color: #1e72e3">wounded_unit</span> 20 mana';
    private const MANA_RESTORE_ABILITY_RU = '<span style="color: #1e72e3">wounded_unit</span> использовал <img src="/images/icons/ability/234.png" alt="" /> <span class="ability">Зелье оздоровления</span> и восстановил <span style="color: #1e72e3">wounded_unit</span> 20 маны';

    private const MANA_RESTORE_EFFECT_EN = '<span style="color: #1e72e3">wounded_unit</span> restored 20 mana from effect <img src="/images/icons/ability/234.png" alt="" /> <span class="ability">Restore Potion</span>';
    private const MANA_RESTORE_EFFECT_RU = '<span style="color: #1e72e3">wounded_unit</span> восстановил 20 маны от эффекта <img src="/images/icons/ability/234.png" alt="" /> <span class="ability">Зелье оздоровления</span>';

    private const SUMMON_EN = '<span style="color: #1e72e3">unit_1</span> summon <img src="/images/icons/ability/275.png" alt="" /> <span class="ability">Imp</span>';
    private const SUMMON_RU = '<span style="color: #1e72e3">unit_1</span> призвал <img src="/images/icons/ability/275.png" alt="" /> <span class="ability">Беса</span>';

    private const WAIT_EN = '<span style="color: #1e72e3">unit_1</span> preparing to attack';
    private const WAIT_RU = '<span style="color: #1e72e3">unit_1</span> готовится к атаке';

    private const PARALYSIS_EN = '<span style="color: #1e72e3">unit_1</span> paralyzed and unable to move';
    private const PARALYSIS_RU = '<span style="color: #1e72e3">unit_1</span> парализован и не может двигаться';

    private const EFFECT_DAMAGE_EN = '<span style="color: #1e72e3">unit_1</span> received 20 damage from effect <img src="/images/icons/ability/202.png" alt="" /> <span class="ability">Poison</span>';
    private const EFFECT_DAMAGE_RU = '<span style="color: #1e72e3">unit_1</span> получил 20 урона от эффекта <img src="/images/icons/ability/202.png" alt="" /> <span class="ability">Отравление</span>';

    // В текущих способностях сообщение от BuffAction не формируется, оно формируется через EffectAction
    // По этому это сообщение выглядит кривовато, но это нормально
    private const BUFF_EN = '<span style="color: #1e72e3">unit_1</span> Reserve Forces';
    private const BUFF_RU = '<span style="color: #1e72e3">unit_1</span> Резервные Силы';

    // Сейчас сообщения выглядят некорректно, т.к. сообщение о воскрешении подразумевает, что воскрешение использовано со способности
    private const RESURRECTION_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/053.png" alt="" /> <span class="ability">ExampleActionName</span> and resurrected <span style="color: #1e72e3">dead_unit</span>';
    private const RESURRECTION_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/053.png" alt="" /> <span class="ability">ExampleActionName</span> и воскресил <span style="color: #1e72e3">dead_unit</span>';

    private const EFFECT_HEAL_EN = '<span style="color: #1e72e3">wounded_unit</span> restored 15 life from effect <img src="/images/icons/ability/234.png" alt="" /> <span class="ability">Healing Potion</span>';
    private const EFFECT_HEAL_RU = '<span style="color: #1e72e3">wounded_unit</span> восстановил 15 здоровья от эффекта <img src="/images/icons/ability/234.png" alt="" /> <span class="ability">Лечебное зелье</span>';

    private const APPLY_EFFECT_SELF_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/156.png" alt="" /> <span class="ability">Reserve Forces</span>';
    private const APPLY_EFFECT_SELF_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/156.png" alt="" /> <span class="ability">Резервные Силы</span>';

    private const APPLY_EFFECT_TO_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/156.png" alt="" /> <span class="ability">Reserve Forces</span> on <span style="color: #1e72e3">unit_2</span>';
    private const APPLY_EFFECT_TO_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/156.png" alt="" /> <span class="ability">Резервные Силы</span> на <span style="color: #1e72e3">unit_2</span>';

    private const SKIP_MESSAGE = '';

    /**
     * Тест на формирование сообщения об уроне
     *
     * @throws Exception
     */
    public function testChatAddMessageDamageDefault(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::DAMAGE_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::DAMAGE_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения об уроне с вампиризмом здоровья
     *
     * @throws Exception
     */
    public function testChatAddMessageLifeDamageVampirism(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(42, 2);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::DAMAGE_AND_LIFE_VAMPIRISM_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::DAMAGE_AND_LIFE_VAMPIRISM_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения об уроне с вампиризмом маны
     *
     * @throws Exception
     */
    public function testChatAddMessageManaDamageVampirism(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(2, 5);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::DAMAGE_AND_MANA_VAMPIRISM_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::DAMAGE_AND_MANA_VAMPIRISM_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения об уроне с вампиризмом здоровья и маны
     *
     * @throws Exception
     */
    public function testChatAddMessageLifeAndManaDamageVampirism(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(52, 5);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::DAMAGE_AND_LIFE_AND_MANA_VAMPIRISM_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::DAMAGE_AND_LIFE_AND_MANA_VAMPIRISM_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения о критическом уроне
     *
     * @throws Exception
     */
    public function testChatAddMessageCriticalDamage(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(40, 2);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::CRITICAL_DAMAGE_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::CRITICAL_DAMAGE_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения о блоке
     *
     * @throws Exception
     */
    public function testChatAddMessageBlockedDamage(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 28);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

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

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

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
    public function testChatAddMessageDamageAbilityDefault(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $action = $this->createAbilityDamage($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::DAMAGE_ABILITY_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::DAMAGE_ABILITY_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения об уроне со способности с вампиризмом
     *
     * @throws Exception
     */
    public function testChatAddMessageDamageAbilityVampire(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $action = $this->createAbilityDamageVampirism($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::DAMAGE_ABILITY_AND_VAMPIRISM_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::DAMAGE_ABILITY_AND_VAMPIRISM_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения об уроне со способности
     *
     * @throws Exception
     */
    public function testChatAddMessageCriticalDamageAbility(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(40, 2);

        $action = $this->createCriticalAbilityDamage($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::CRITICAL_DAMAGE_ABILITY_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::CRITICAL_DAMAGE_ABILITY_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения о блоке способности
     *
     * @throws Exception
     */
    public function testChatAddMessageBlockedDamageAbility(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 28);

        $action = $this->createAbilityDamage($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

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

        $action = $this->createAbilityDamage($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

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
    public function testChatAddMessageHealBase(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $otherUnit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit, $otherUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new HealAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            HealAction::TARGET_WOUNDED_ALLIES,
            20,
            '',
            HealAction::UNIT_ANIMATION_METHOD,
            HealAction::DEFAULT_MESSAGE_METHOD
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::HEAL_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::HEAL_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения о лечении от способности другого юнита
     *
     * @throws Exception
     */
    public function testChatAddMessageHealOtherAbility(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $otherUnit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit, $otherUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new HealAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            HealAction::TARGET_WOUNDED_ALLIES,
            60,
            'Great Heal',
            'heal',
            'healAbility',
            '/images/icons/ability/196.png'
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::HEAL_ABILITY_OTHER_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::HEAL_ABILITY_OTHER_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения о лечении себя от способности
     *
     * @throws Exception
     */
    public function testChatAddMessageHealSelfAbility(): void
    {
        $unit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new HealAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            HealAction::TARGET_SELF,
            60,
            'Great Heal',
            'heal',
            'healAbility',
            '/images/icons/ability/196.png'
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::HEAL_ABILITY_SELF_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::HEAL_ABILITY_SELF_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения о восстановлении маны
     *
     * @throws Exception
     */
    public function testChatAddMessageManRestoreDefault(): void
    {
        $unit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new ManaRestoreAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            HealAction::TARGET_SELF,
            20,
            ManaRestoreAction::NAME,
            ManaRestoreAction::SKIP_ANIMATION_METHOD,
            ManaRestoreAction::DEFAULT_MESSAGE_METHOD
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::MANA_RESTORE_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::MANA_RESTORE_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения о восстановлении маны со способности
     *
     * @throws Exception
     */
    public function testChatAddMessageManRestoreAbilityOther(): void
    {
        $unit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new ManaRestoreAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            HealAction::TARGET_SELF,
            20,
            'Restore Potion',
            ManaRestoreAction::SKIP_ANIMATION_METHOD,
            ManaRestoreAction::ABILITY_MESSAGE_METHOD,
            '/images/icons/ability/234.png'
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::MANA_RESTORE_ABILITY_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::MANA_RESTORE_ABILITY_RU, $this->getChatRu()->addMessage($action));
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
           $this->container,
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

        $action = new WaitAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command
        );

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

        $action = new ParalysisAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            ParalysisAction::PARALYSIS_MESSAGE_METHOD
        );

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
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'Reserve Forces',
            BuffAction::MAX_LIFE,
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
     * @throws Exception
     */
    public function testChatAddMessageResurrection(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $deadUnit = UnitFactory::createByTemplate(10);
        $enemyUnit = UnitFactory::createByTemplate(2);

        $command = CommandFactory::create([$unit, $deadUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new ResurrectionAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_DEAD_ALLIES,
            50,
            'ExampleActionName',
            ResurrectionAction::DEFAULT_MESSAGE_METHOD,
            '/images/icons/ability/053.png'
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
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            true,
            'Poison',
            DamageAction::UNIT_ANIMATION_METHOD,
            DamageAction::EFFECT_MESSAGE_METHOD,
            $unit->getOffense(),
            null,
            '/images/icons/ability/202.png'
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
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            HealAction::TARGET_SELF,
            15,
            'Healing Potion',
            HealAction::EFFECT_ANIMATION_METHOD,
            HealAction::EFFECT_MESSAGE_METHOD,
            '/images/icons/ability/234.png'
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::EFFECT_HEAL_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::EFFECT_HEAL_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на формирование сообщения о восстановлении маны от эффекта
     *
     * @throws Exception
     */
    public function testChatAddMessageEffectManaRestore(): void
    {
        $unit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new ManaRestoreAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            HealAction::TARGET_SELF,
            20,
            'Restore Potion',
            ManaRestoreAction::SKIP_ANIMATION_METHOD,
            ManaRestoreAction::EFFECT_MESSAGE_METHOD,
            '/images/icons/ability/234.png'
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::MANA_RESTORE_EFFECT_EN, $this->getChat()->addMessage($action));
        self::assertEquals(self::MANA_RESTORE_EFFECT_RU, $this->getChatRu()->addMessage($action));
    }

    /**
     * Тест на пропуск формирования сообщения для чата - будет возвращена пустая строка, а в сам чат (массив сообщений)
     * ничего не будет добавлено
     *
     * @throws Exception
     */
    public function testChatSkipMessage(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2, $this->container);

        $action = new BuffAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'test name',
            BuffAction::MAX_LIFE,
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

        $chat = new Chat($this->container);

        $action = new DamageAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            true,
            'test attack',
            DamageAction::UNIT_ANIMATION_METHOD,
            $messageMethod = 'undefinedMessageMethod',
            $unit->getOffense()
        );

        $this->expectException(ChatException::class);
        $this->expectExceptionMessage(ChatException::UNDEFINED_MESSAGE_METHOD . ': ' . $messageMethod);
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

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_ALL_ENEMY);

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
    public function testChatAddMessageDamageAndBlockedDefault(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $firstEnemyUnit = UnitFactory::createByTemplate(2);
        $secondaryEnemyUnit = UnitFactory::createByTemplate(28);

        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$firstEnemyUnit, $secondaryEnemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_ALL_ENEMY);

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

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_ALL_ENEMY);

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

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_ALL_ENEMY);

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

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_ALL_ENEMY);

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
    public function testChatAddMessageAbilityDamageAndBlockedDefault(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $firstEnemyUnit = UnitFactory::createByTemplate(2);
        $secondaryEnemyUnit = UnitFactory::createByTemplate(28);

        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$firstEnemyUnit, $secondaryEnemyUnit]);

        $action = $this->createAbilityDamage($unit, $enemyCommand, $command, DamageAction::TARGET_ALL_ENEMY);

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

        $action = $this->createAbilityDamage($unit, $enemyCommand, $command, DamageAction::TARGET_ALL_ENEMY);

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

        $action = $this->createAbilityDamage($unit, $enemyCommand, $command, DamageAction::TARGET_ALL_ENEMY);

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

        $action = $this->createAbilityDamage($unit, $enemyCommand, $command, DamageAction::TARGET_ALL_ENEMY);

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

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_ALL_ENEMY);

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
        $data = [
            'type'           => ActionInterface::EFFECT,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => $typeTarget,
            'name'           => 'Reserve Forces',
            'use_message'    => 'use',
            'message_method' => 'applyEffect',
            'icon'           => '/images/icons/ability/156.png',
            'effect'         => [
                'name'                  => 'Effect#123',
                'icon'                  => '/images/icons/ability/156.png',
                'duration'              => 8,
                'on_apply_actions'      => [
                    [
                        'type'           => ActionInterface::BUFF,
                        'action_unit'    => $unit,
                        'enemy_command'  => $enemyCommand,
                        'allies_command' => $command,
                        'type_target'    => ActionInterface::TARGET_SELF,
                        'name'           => 'use Reserve Forces',
                        'modify_method'  => BuffAction::MAX_LIFE,
                        'power'          => 130,
                    ],
                ],
                'on_next_round_actions' => [],
                'on_disable_actions'    => [],
            ],
        ];

        return $this->container->getActionFactory()->create($data);
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @param int $typeTarget
     * @return DamageAction
     * @throws Exception
     */
    private function createDamageAction(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command,
        int $typeTarget
    ): DamageAction
    {
        return new DamageAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            $typeTarget,
            true,
            DamageAction::DEFAULT_NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            DamageAction::DEFAULT_MESSAGE_METHOD,
            $unit->getOffense()
        );
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @param int $typeTarget
     * @return ActionInterface
     * @throws Exception
     */
    private function createAbilityDamage(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command,
        int $typeTarget
    ): ActionInterface
    {
        return new DamageAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            $typeTarget,
            true,
            'Heavy Strike',
            DamageAction::UNIT_ANIMATION_METHOD,
            'damageAbility',
            OffenseFactory::create([
                'damage_type'         => 1,
                'weapon_type'         => WeaponTypeInterface::SWORD,
                'physical_damage'     => 50,
                'fire_damage'         => 0,
                'water_damage'        => 0,
                'air_damage'          => 0,
                'earth_damage'        => 0,
                'life_damage'         => 0,
                'death_damage'        => 0,
                'attack_speed'        => 1,
                'cast_speed'          => 0,
                'accuracy'            => 500,
                'magic_accuracy'      => 100,
                'block_ignoring'      => 0,
                'critical_chance'     => 0,
                'critical_multiplier' => 0,
                'damage_multiplier'   => 100,
                'vampirism'           => 0,
                'magic_vampirism'     => 0,
            ], $this->container),
            null,
            '/images/icons/ability/335.png'
        );
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @param int $typeTarget
     * @return ActionInterface
     * @throws Exception
     */
    private function createAbilityDamageVampirism(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command,
        int $typeTarget
    ): ActionInterface
    {
        return new DamageAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            $typeTarget,
            true,
            'Heavy Strike',
            DamageAction::UNIT_ANIMATION_METHOD,
            'damageAbility',
            OffenseFactory::create([
                'damage_type'         => 1,
                'weapon_type'         => WeaponTypeInterface::SWORD,
                'physical_damage'     => 50,
                'fire_damage'         => 0,
                'water_damage'        => 0,
                'air_damage'          => 0,
                'earth_damage'        => 0,
                'life_damage'         => 0,
                'death_damage'        => 0,
                'attack_speed'        => 1,
                'cast_speed'          => 0,
                'accuracy'            => 500,
                'magic_accuracy'      => 100,
                'block_ignoring'      => 0,
                'critical_chance'     => 0,
                'critical_multiplier' => 0,
                'damage_multiplier'   => 100,
                'vampirism'           => 50,
                'magic_vampirism'     => 0,
            ], $this->container),
            null,
            '/images/icons/ability/335.png'
        );
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @param int $typeTarget
     * @return ActionInterface
     * @throws Exception
     */
    private function createCriticalAbilityDamage(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command,
        int $typeTarget
    ): ActionInterface
    {
        return new DamageAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            $typeTarget,
            true,
            'Heavy Strike',
            DamageAction::UNIT_ANIMATION_METHOD,
            'damageAbility',
            OffenseFactory::create([
                'damage_type'         => 1,
                'weapon_type'         => WeaponTypeInterface::SWORD,
                'physical_damage'     => 50,
                'fire_damage'         => 0,
                'water_damage'        => 0,
                'air_damage'          => 0,
                'earth_damage'        => 0,
                'life_damage'         => 0,
                'death_damage'        => 0,
                'attack_speed'        => 1,
                'cast_speed'          => 0,
                'accuracy'            => 500,
                'magic_accuracy'      => 100,
                'block_ignoring'      => 0,
                'critical_chance'     => 100,
                'critical_multiplier' => 200,
                'damage_multiplier'   => 100,
                'vampirism'           => 0,
                'magic_vampirism'     => 0,
            ], $this->container),
            null,
            '/images/icons/ability/335.png'
        );
    }
}
