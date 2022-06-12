<?php

declare(strict_types=1);

namespace Battle\Unit\Ability;

use Battle\Traits\ValidationTrait;
use Battle\Unit\UnitInterface;
use Exception;

class AbilityFactory
{
    use ValidationTrait;

    private $typesActivate = [
        AbilityInterface::ACTIVATE_CONCENTRATION,
        AbilityInterface::ACTIVATE_RAGE,
        AbilityInterface::ACTIVATE_LOW_LIFE,
        AbilityInterface::ACTIVATE_DEAD,
    ];

    /**
     * Создание способности через массив параметров необходимо для создания класса и его способностей из массива
     * параметров.
     *
     * Изначально есть только массив параметров, без юнитов и команд. Но в момент создания способности уже есть юнит, по
     * этому он запрашивается - подразумевается, что создание способностей происходит в конструкторе класса Unit.
     *
     * @param UnitInterface $unit
     * @param array $data
     * @return AbilityInterface
     * @throws Exception
     */
    public function create(UnitInterface $unit, array $data): AbilityInterface
    {
        $name = self::string($data, 'name', AbilityException::INVALID_NAME_DATA);
        $icon = self::string($data, 'icon', AbilityException::INVALID_ICON_DATA);
        $disposable = self::bool($data, 'disposable', AbilityException::INVALID_DISPOSABLE_DATA);
        $typeActivate = self::int($data, 'type_activate', AbilityException::INVALID_TYPE_ACTIVATE_DATA);
        self::in($typeActivate, $this->typesActivate, AbilityException::UNKNOWN_ACTIVATE_TYPE . ': ' . $typeActivate);
        $chanceActivate = self::intOrMissing($data, 'chance_activate', AbilityException::INVALID_CHANCE_ACTIVATE_DATA);
        $actionsData = self::array($data, 'actions', AbilityException::INVALID_ACTIONS_DATA);

        // Сразу проверяем, что массив параметров способностей состоит из массивов
        foreach ($actionsData as $actionData) {
            if (!is_array($actionData)) {
                throw new AbilityException(AbilityException::INVALID_ACTION_DATA);
            }
        }

        return new Ability(
            $unit,
            $disposable,
            $name,
            $icon,
            $actionsData,
            $typeActivate,
            $chanceActivate
        );
    }
}
