<?php

declare(strict_types=1);

namespace Battle\Command;

use Battle\Unit\UnitFactory;
use Exception;

class CommandFactory
{
    /**
     * @param array $data
     * @return CommandInterface
     * @throws CommandException
     */
    public static function create(array $data): CommandInterface
    {
        $units = [];
        $i = 1;

        foreach ($data as $datum) {

            if (!is_array($datum)) {
                throw new CommandException(CommandException::INCORRECT_UNIT_DATA);
            }

            try {
                $units[] = UnitFactory::create($datum);
            } catch (Exception $e) {
                throw new CommandException($e->getMessage() . ' (' . $i . ' element)');
            }

            $i++;
        }

        return new Command($units);
    }
}
