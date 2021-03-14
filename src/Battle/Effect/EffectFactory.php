<?php

declare(strict_types=1);

namespace Battle\Effect;

use Battle\Effect\Change\Change;
use Battle\Effect\Change\ChangeCollection;
use Battle\Effect\Change\ChangeException;
use Battle\Unit\UnitInterface;

class EffectFactory
{
    private static $effects = [
        1 => [
            'id'              => 1,
            'name'            => 'Increased Life',
            'description'     => 'Increased life on 20%',
            'duration'        => 5,
            'change_apply'    => [
                [
                    'type'       => EffectType::LIFE,
                    'increased'  => true,
                    'multiplier' => true,
                    'power'      => 20, // +20%
                ],
            ],
            'change_duration' => [],
        ],
        2 => [
            'id'              => 2,
            'name'            => 'Increased Damage',
            'description'     => 'Increased damage on 10',
            'duration'        => 7,
            'change_apply'    => [
                [
                    'type'       => EffectType::DAMAGE,
                    'increased'  => true,
                    'multiplier' => false,
                    'power'      => 10, // +10
                ],
            ],
            'change_duration' => [],
        ],
    ];

    /**
     * @param int $id
     * @param UnitInterface $unit
     * @return Effect
     * @throws ChangeException
     * @throws EffectException
     */
    public static function create(int $id, UnitInterface $unit): Effect
    {
        if (!array_key_exists($id, self::$effects)) {
            throw new EffectException(EffectException::NO_EFFECT);
        }

        return self::createEffect(self::$effects[$id], $unit);
    }

    public static function getAll(): array
    {
        return self::$effects;
    }

    /**
     * @param array $data
     * @param UnitInterface $unit
     * @return Effect
     * @throws ChangeException
     * @throws EffectException
     */
    private static function createEffect(array $data, UnitInterface $unit): Effect
    {
        if (
            !array_key_exists('id', $data) || !is_int($data['id']) ||
            !array_key_exists('name', $data) || !is_string($data['name']) ||
            !array_key_exists('description', $data) || !is_string($data['description']) ||
            !array_key_exists('duration', $data) || !is_int($data['duration']) || $data['duration'] < 1 ||
            !array_key_exists('change_apply', $data) || !is_array($data['change_apply']) ||
            !array_key_exists('change_duration', $data) || !is_array($data['change_duration'])
        ) {
            throw new EffectException(EffectException::INVALID_EFFECT_DATA);
        }

        $changesApply = self::createChangeCollection($data['change_apply']);
        $changesDuration = self::createChangeCollection($data['change_duration']);

        return new Effect($data['id'], $data['name'], $data['description'], $unit, $data['duration'], $changesApply, $changesDuration);
    }

    /**
     * @param array $changes
     * @return ChangeCollection
     * @throws ChangeException
     */
    private static function createChangeCollection(array $changes): ChangeCollection
    {
        $collection = new ChangeCollection();

        foreach ($changes as $change) {

            if (!array_key_exists('type', $change) || !is_int($change['type'])) {
                throw new ChangeException(ChangeException::INVALID_TYPE_DATA);
            }

            if (!array_key_exists('increased', $change) || !is_bool($change['increased'])) {
                throw new ChangeException(ChangeException::INVALID_INCREASED_DATA);
            }

            if (!array_key_exists('multiplier', $change) || !is_bool($change['multiplier'])) {
                throw new ChangeException(ChangeException::INVALID_MULTIPLIER_DATA);
            }

            if (!array_key_exists('power', $change) || !is_int($change['power'])) {
                throw new ChangeException(ChangeException::INVALID_POWER_DATA);
            }

            $collection->add(new Change($change['type'], $change['increased'], $change['multiplier'], $change['power']));

        }

        return $collection;
    }
}
