<?php

declare(strict_types=1);

namespace Battle;

use Battle\Chat\Chat;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Round\RoundFactory;
use Battle\Statistic\Statistic;
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
     *         'class'        => 1,
     *         'command'      => 'left',
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
     *         'class'        => 1,
     *         'command'      => 'right',
     *     ],
     * ];
     *
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
     * Create (left or right) command based on $data
     *
     * @param array $data
     * @param string $command
     * @return CommandInterface
     * @throws BattleException
     * @throws CommandException
     * @throws UnitException
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
