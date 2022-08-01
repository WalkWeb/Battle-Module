<?php

declare(strict_types=1);

namespace Battle\Unit\Defense;

use Battle\Traits\ValidationTrait;
use Exception;

class DefenseFactory
{
    use ValidationTrait;

    /**
     * Создает Defense на основе массива с данными
     *
     * @param array $data
     * @return Defense
     * @throws Exception
     */
    public static function create(array $data): Defense
    {
        self::int($data, 'defense', DefenseException::INCORRECT_DEFENSE);
        self::int($data, 'magic_defense', DefenseException::INCORRECT_MAGIC_DEFENSE);
        self::int($data, 'block', DefenseException::INCORRECT_BLOCK);
        self::int($data, 'magic_block', DefenseException::INCORRECT_MAGIC_BLOCK);
        self::int($data, 'mental_barrier', DefenseException::INCORRECT_MENTAL_BARRIER);

        self::intMinMaxValue(
            $data['defense'],
            DefenseInterface::MIN_DEFENSE,
            DefenseInterface::MAX_DEFENSE,
            DefenseException::INCORRECT_DEFENSE_VALUE . DefenseInterface::MIN_DEFENSE . '-' . DefenseInterface::MAX_DEFENSE
        );

        self::intMinMaxValue(
            $data['magic_defense'],
            DefenseInterface::MIN_MAGIC_DEFENSE,
            DefenseInterface::MAX_MAGIC_DEFENSE,
            DefenseException::INCORRECT_MAGIC_DEFENSE_VALUE . DefenseInterface::MIN_MAGIC_DEFENSE . '-' . DefenseInterface::MAX_MAGIC_DEFENSE
        );

        self::intMinMaxValue(
            $data['block'],
            DefenseInterface::MIN_BLOCK,
            DefenseInterface::MAX_BLOCK,
            DefenseException::INCORRECT_BLOCK_VALUE . DefenseInterface::MIN_BLOCK . '-' . DefenseInterface::MAX_BLOCK
        );

        self::intMinMaxValue(
            $data['magic_block'],
            DefenseInterface::MIN_MAGIC_BLOCK,
            DefenseInterface::MAX_MAGIC_BLOCK,
            DefenseException::INCORRECT_MAGIC_BLOCK_VALUE . DefenseInterface::MIN_MAGIC_BLOCK . '-' . DefenseInterface::MAX_MAGIC_BLOCK
        );

        self::intMinMaxValue(
            $data['mental_barrier'],
            DefenseInterface::MIN_MENTAL_BARRIER,
            DefenseInterface::MAX_MENTAL_BARRIER,
            DefenseException::INCORRECT_MENTAL_BARRIER_VALUE . DefenseInterface::MIN_MENTAL_BARRIER . '-' . DefenseInterface::MAX_MENTAL_BARRIER
        );

        return new Defense($data['defense'], $data['magic_defense'], $data['block'], $data['magic_block'], $data['mental_barrier']);
    }
}
