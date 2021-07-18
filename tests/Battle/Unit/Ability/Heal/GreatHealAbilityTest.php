<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Heal;

use Battle\Action\Damage\DamageAction;
use Battle\Result\Chat\Message;
use Exception;
use PHPUnit\Framework\TestCase;
use Battle\Action\Heal\HealAction;
use Battle\Command\CommandFactory;
use Tests\Battle\Factory\UnitFactory;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Heal\GreatHealAbility;

class GreatHealAbilityTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testGreatHealAbilityCreate(): void
    {
        $name = 'Great Heal Ability';
        $icon = '/images/icons/ability/196.png';
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new GreatHealAbility($name, $icon, $unit);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());

        self::assertFalse($ability->canByUsed($enemyCommand, $command));

        // Наносим урон юниту, чтобы способность перешла в "возможную для использования"
        $damage = new DamageAction($enemyUnit, $command, $enemyCommand, new Message());
        $damage->handle();

        // После чего, способность может быть использована
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

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
            self::assertInstanceOf(HealAction::class, $action);
            self::assertEquals($unit->getDamage() * 3, $action->getPower());
        }
    }

    /**
     * Тест на ситуацию, когда GreatHealAbility не может быть использован
     *
     * @throws Exception
     */
    public function testGreatHealAbilityCantBeUsed(): void
    {
        $name = 'Great Heal Ability';
        $icon = '/images/icons/ability/196.png';
        $unit = UnitFactory::createByTemplate(4);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        // Создаем напрямую, и проверяем, что способность не может быть применена
        $ability = new GreatHealAbility($name, $icon, $unit);
        self::assertFalse($ability->canByUsed($enemyCommand, $command));

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        // По этому, получая способности через getAction - получаем DamageAction, а не GreatHealAbility
        $abilities = $unit->getAction($enemyCommand, $command);

        foreach ($abilities as $ability) {
            self::assertInstanceOf(DamageAction::class, $ability);
        }
    }
}
