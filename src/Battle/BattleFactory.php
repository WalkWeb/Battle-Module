<?php

declare(strict_types=1);

namespace Battle;

use Battle\Container\Container;
use Battle\Container\ContainerInterface;
use Battle\Result\Chat\Message;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Unit\UnitException;
use Exception;

class BattleFactory
{
    /**
     * Create Battle object and dependencies
     *
     * Example data:
     *
     * $data = [
     *     [
     *         'id'           => '3bc9b8be-8cbd-44b4-a935-cd435d905d1b',
     *         'name'         => 'Warrior',
     *         'avatar'       => '/images/avas/humans/human001.jpg',
     *         'damage'       => 15,
     *         'attack_speed' => 1.0,
     *         'life'         => 110,
     *         'total_life'   => 110,
     *         'melee'        => true,
     *         'command'      => 1,
     *         'class'        => 1,
     *         'race'         => 1,
     *     ],
     *     [
     *         'id'           => '3bc9b8be-8cbd-44b4-a935-cd435d905d2b',
     *         'name'         => 'Skeleton',
     *         'avatar'       => '/images/avas/monsters/005.png',
     *         'damage'       => 25,
     *         'attack_speed' => 1,
     *         'life'         => 165,
     *         'total_life'   => 165,
     *         'melee'        => true,
     *         'command'      => 2,
     *         'class'        => 1,
     *         'race'         => 8,
     *     ],
     * ];
     *
     * @param array $data
     * @param ContainerInterface|null $container
     * @param bool|null $debug
     * @return BattleInterface
     * @throws Exception
     */
    public static function create(
        array $data,
        ?ContainerInterface $container = null,
        ?bool $debug = true
    ): BattleInterface
    {
        $container = $container ?? new Container();

        return new Battle(
            self::createCommand($data, BattleInterface::LEFT_COMMAND, $container->getMessage()),
            self::createCommand($data, BattleInterface::RIGHT_COMMAND, $container->getMessage()),
            $container,
            $debug
        );
    }

    /**
     * Create (left or right) command based on $data
     *
     * @param array $data
     * @param $command
     * @param Message|null $message
     * @return CommandInterface
     * @throws BattleException
     * @throws CommandException
     * @throws UnitException
     */
    public static function createCommand(array $data, $command, ?Message $message = null): CommandInterface
    {
        $message = $message ?? new Message();
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

        return CommandFactory::create($commandData, $message);
    }
}
