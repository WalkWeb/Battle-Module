<?php

declare(strict_types=1);

namespace Battle\Command;

use Battle\Container\Container;
use Battle\Container\ContainerInterface;
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
     * @param ContainerInterface|null $container TODO Убрать nullable
     * @return CommandInterface
     * @throws CommandException
     * @throws UnitException
     */
    public static function create(array $data, ?ContainerInterface $container = null): CommandInterface
    {
        $container = $container ?? new Container();
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
                $units->add(UnitFactory::create($datum, $container));
            } catch (Exception $e) {
                throw new CommandException($e->getMessage() . ' (' . $i . ' element)');
            }

            $i++;
        }

        return new Command($units);
    }
}
