<?php

declare(strict_types=1);

namespace Battle;

use Battle\Container\Container;
use Battle\Container\ContainerInterface;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Response\Statistic\Statistic;
use Battle\Unit\UnitException;
use Exception;

class BattleFactory
{
    /**
     * Create Battle object and dependencies
     *
     * [
     *     [
     *         'id'         => '60f3c032-46a6-454d-ae3a-d066f150f6ef',
     *         'name'       => 'Titan',
     *         'level'      => 3,
     *         'avatar'     => '/images/avas/orcs/orc001.jpg',
     *         'life'       => 185,
     *         'total_life' => 185,
     *         'mana'       => 38,
     *         'total_mana' => 38,
     *         'melee'      => true,
     *         'command'    => 1,
     *         'class'      => 5,
     *         'race'       => 3,
     *         'offense'    => [
     *             'damage_type'         => 1,
     *             'physical_damage'     => 35,
     *             'attack_speed'        => 1.2,
     *             'accuracy'            => 176,
     *             'magic_accuracy'      => 12,
     *             'block_ignoring'      => 0,
     *             'critical_chance'     => 5,
     *             'critical_multiplier' => 200,
     *         ],
     *         'defense'    => [
     *             'physical_resist' => 0,
     *             'defense'         => 134,
     *             'magic_defense'   => 31,
     *             'block'           => 0,
     *             'magic_block'     => 0,
     *             'mental_barrier'  => 0,
     *         ],
     *     ],
     *     [
     *         'id'         => 'f7e84eab-e4f6-469f-b0e3-f5f965f9fbce',
     *         'name'       => 'Warrior',
     *         'level'      => 2,
     *         'avatar'     => '/images/avas/humans/human001.jpg',
     *         'life'       => 160,
     *         'total_life' => 160,
     *         'mana'       => 61,
     *         'total_mana' => 61,
     *         'melee'      => true,
     *         'command'    => 2,
     *         'class'      => 1,
     *         'race'       => 1,
     *         'offense'    => [
     *             'damage_type'         => 1,
     *             'physical_damage'     => 28,
     *             'attack_speed'        => 1.1,
     *             'accuracy'            => 212,
     *             'magic_accuracy'      => 14,
     *             'block_ignoring'      => 0,
     *             'critical_chance'     => 10,
     *             'critical_multiplier' => 200,
     *         ],
     *         'defense'    => [
     *             'physical_resist' => 0,
     *             'defense'         => 155,
     *             'magic_defense'   => 63,
     *             'block'           => 25,
     *             'magic_block'     => 0,
     *             'mental_barrier'  => 0,
     *         ],
     *     ]
     * ];
     *
     * @param array $data
     * @param ContainerInterface|null $container
     * @param bool $testMode
     * @return BattleInterface
     * @throws Exception
     */
    public static function create(
        array $data,
        ?ContainerInterface $container = null,
        bool $testMode = false
    ): BattleInterface
    {
        if (!$container) {
            // Statistic создается первым, чтобы подсчет времени выполнения скрипта и расход памяти был максимально полным
            $statistic = new Statistic();
            $container = new Container($testMode);
            $container->set('Statistic', $statistic);
        }

        return new Battle(
            self::createCommand($data, BattleInterface::LEFT_COMMAND, $container),
            self::createCommand($data, BattleInterface::RIGHT_COMMAND, $container),
            $container
        );
    }

    /**
     * Create (left or right) command based on $data
     *
     * @param array $data
     * @param $command
     * @param ContainerInterface $container
     * @return CommandInterface
     * @throws BattleException
     * @throws CommandException
     * @throws UnitException
     */
    public static function createCommand(array $data, $command, ContainerInterface $container): CommandInterface
    {
        $commandData = [];

        foreach ($data as $datum) {

            if (!is_array($datum)) {
                throw new BattleException(BattleException::INCORRECT_UNIT_DATA);
            }

            if (!array_key_exists(BattleInterface::COMMAND_PARAMETER, $datum)) {
                throw new BattleException(BattleException::NO_COMMAND_PARAMETER);
            }

            if ($datum[BattleInterface::COMMAND_PARAMETER] === $command) {
                $commandData[] = $datum;
            }
        }

        return CommandFactory::create($commandData, $container);
    }
}
