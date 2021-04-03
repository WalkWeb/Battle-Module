<?php

declare(strict_types=1);

namespace Battle;

use Battle\Chat\Chat;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Round\RoundFactory;
use Battle\Statistic\Statistic;
use Exception;

class BattleFactory
{
    /**
     * @param array $data
     * @param Statistic|null $statistics
     * @param Chat|null $chat
     * @param bool|null $debug
     * @param RoundFactory|null $roundFactory
     * @return BattleInterface
     * @throws Exception
     */
    public static function create(
        array $data,
        ?Statistic $statistics = null,
        ?Chat $chat = null,
        ?bool $debug = true,
        ?RoundFactory $roundFactory = null
    ): BattleInterface
    {
        return new Battle(
            self::createCommand($data, BattleInterface::LEFT_COMMAND),
            self::createCommand($data, BattleInterface::RIGHT_COMMAND),
            $statistics ?? new Statistic(),
            $chat ?? new Chat(),
            $debug,
            $roundFactory
        );
    }

    /**
     * @param array $data
     * @param string $command
     * @return CommandInterface
     * @throws BattleException
     * @throws Command\CommandException
     */
    public static function createCommand(array $data, string $command): CommandInterface
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

        return CommandFactory::create($commandData);
    }
}
