<?php

declare(strict_types=1);

namespace Battle;

use Battle\Container\Container;
use Battle\Container\ContainerInterface;
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
     * TODO Example data
     *
     * @param array $data
     * @param ContainerInterface|null $container
     * @return BattleInterface
     * @throws Exception
     */
    public static function create(
        array $data,
        ?ContainerInterface $container = null
    ): BattleInterface
    {
        $container = $container ?? new Container();

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
     * @param ContainerInterface|null $container
     * @return CommandInterface
     * @throws BattleException
     * @throws CommandException
     * @throws UnitException
     */
    public static function createCommand(array $data, $command, ?ContainerInterface $container = null): CommandInterface
    {
        $container = $container ?? new Container();
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
