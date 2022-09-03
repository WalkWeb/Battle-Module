<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Summon;

use Battle\Action\ActionInterface;
use Battle\Action\SummonAction;
use Battle\Command\CommandFactory;
use Battle\Container\Container;
use Battle\Result\Scenario\Scenario;
use Battle\Result\Statistic\Statistic;
use Battle\Unit\Ability\Ability;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Unit\UnitInterface;
use Battle\Weapon\Type\WeaponTypeInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class SummonFireElementalAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_1</span> summon <img src="/images/icons/ability/198.png" alt="" /> <span class="ability">Fire Elemental</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_1</span> призвал <img src="/images/icons/ability/198.png" alt="" /> <span class="ability">Элементаля огня</span>';

    // -----------------------------------------------------------------------------------------------------------------
    // ------------------------------------------   Тесты через Ability   ----------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Тест на создание и применение способности SummonFireElementalAbility через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testSummonFireElementalAbilityUse(): void
    {
        $name = 'Fire Elemental';
        $icon = '/images/icons/ability/198.png';

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
                    'type'   => ActionInterface::SUMMON,
                    'name'   => $name,
                    'icon'   => $icon,
                    'summon' => [
                        'name'       => $name,
                        'level'      => 3,
                        'avatar'     => '/images/avas/summon/fire-elemental.png',
                        'life'       => 62,
                        'total_life' => 62,
                        'mana'       => 17,
                        'total_mana' => 17,
                        'melee'      => true,
                        'class'      => null,
                        'race'       => 10,
                        'offense'    => [
                            'type_damage'         => 2,
                            'weapon_type'         => WeaponTypeInterface::UNARMED,
                            'physical_damage'     => 17,
                            'attack_speed'        => 1.1,
                            'accuracy'            => 200,
                            'magic_accuracy'      => 100,
                            'block_ignore'        => 0,
                            'critical_chance'     => 5,
                            'critical_multiplier' => 150,
                            'vampire'             => 0,
                        ],
                        'defense'    => [
                            'physical_resist' => 0,
                            'defense'         => 100,
                            'magic_defense'   => 50,
                            'block'           => 0,
                            'magic_block'     => 0,
                            'mental_barrier'  => 0,
                        ],
                    ],
                ],
            ],
            AbilityInterface::ACTIVATE_RAGE,
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
        for ($i = 0; $i < 20; $i++) {
            $unit->newRound();
        }

        $collection = new AbilityCollection();
        $collection->add($ability);
        $collection->update($unit);

        self::assertTrue($ability->isReady());

        $actions = $ability->getActions($enemyCommand, $command);

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
        self::assertEquals(0, $unit->getRage());
    }

    // -----------------------------------------------------------------------------------------------------------------
    // -------------------------------   Аналогичные тесты через AbilityDataProvider   ---------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Тест на создание и применение способности SummonFireElementalAbility через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testSummonFireElementalAbilityDataProviderUse(): void
    {
        $name = 'Fire Elemental';
        $icon = '/images/icons/ability/198.png';

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, $name);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
        self::assertFalse($ability->isDisposable());
        self::assertFalse($ability->isUsage());

        // Up concentration
        for ($i = 0; $i < 20; $i++) {
            $unit->newRound();
        }

        $collection = new AbilityCollection();
        $collection->add($ability);
        $collection->update($unit);

        self::assertTrue($ability->isReady());

        $actions = $ability->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertInstanceOf(SummonAction::class, $action);
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));

            // Дополнительное проверяем, что по событию успешно создается анимация
            (new Scenario())->addAnimation($action, new Statistic());
        }

        $ability->usage();

        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
        self::assertEquals(0, $unit->getRage());
    }

    /**
     * @param UnitInterface $unit
     * @param string $abilityName
     * @param int $abilityLevel
     * @return AbilityInterface
     * @throws Exception
     */
    private function createAbilityByDataProvider(UnitInterface $unit, string $abilityName, int $abilityLevel = 1): AbilityInterface
    {
        $container = new Container();

        return $container->getAbilityFactory()->create(
            $unit,
            $container->getAbilityDataProvider()->get($abilityName, $abilityLevel)
        );
    }
}
