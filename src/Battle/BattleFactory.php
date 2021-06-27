<?php

declare(strict_types=1);

namespace Battle;

use Battle\Result\Chat\Chat;
use Battle\Result\Chat\Message;
use Battle\Result\FullLog\FullLog;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Round\RoundFactory;
use Battle\Result\Statistic\Statistic;
use Battle\Translation\Translation;
use Battle\Unit\UnitException;

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
     * @param Statistic|null $statistics
     * @param FullLog|null $fullLog
     * @param Chat|null $chat
     * @param bool|null $debug
     * @param RoundFactory|null $roundFactory
     * @param Translation|null $translation
     * @param Message|null $message
     * @return BattleInterface
     * @throws BattleException
     * @throws CommandException
     * @throws UnitException
     */
    public static function create(
        array $data,
        ?Statistic $statistics = null,
        ?FullLog $fullLog = null,
        ?Chat $chat = null,
        ?bool $debug = true,
        ?RoundFactory $roundFactory = null,
        ?Translation $translation = null,
        ?Message $message = null
    ): BattleInterface
    {
        $translation = $translation ?? new Translation();
        $message = $message ?? new Message($translation);

        return new Battle(
            self::createCommand($data, BattleInterface::LEFT_COMMAND, $message),
            self::createCommand($data, BattleInterface::RIGHT_COMMAND, $message),
            $statistics ?? new Statistic(),
            $fullLog ?? new FullLog(),
            $chat ?? new Chat(),
            $debug,
            $roundFactory,
            $translation
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
