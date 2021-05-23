<?php

declare(strict_types=1);

namespace Battle\Command;

use Battle\Result\Chat\Message;
use Battle\Unit\UnitCollection;
use Battle\Unit\UnitException;
use Battle\Unit\UnitFactory;
use Battle\Unit\UnitInterface;
use Exception;

class CommandFactory
{
    /**
     * Создает команду на основании массива юнитов или данных по юнитам
     *
     * @param array $data
     * @param Message|null $message
     * @return CommandInterface
     * @throws CommandException
     * @throws UnitException
     */
    public static function create(array $data, ?Message $message = null): CommandInterface
    {
        $message = $message ?? new Message();
        $units = new UnitCollection();
        $i = 1;

        foreach ($data as $datum) {

            if (is_object($datum) && !($datum instanceof UnitInterface)) {
                throw new CommandException(CommandException::INCORRECT_OBJECT_UNIT);
            }

            if ($datum instanceof UnitInterface) {
                $units->add($datum);
                continue;
            }

            if (!is_array($datum)) {
                throw new CommandException(CommandException::INCORRECT_UNIT_DATA);
            }

            try {
                $units->add(UnitFactory::create($datum, $message));
            } catch (Exception $e) {
                throw new CommandException($e->getMessage() . ' (' . $i . ' element)');
            }

            $i++;
        }

        return new Command($units);
    }
}
