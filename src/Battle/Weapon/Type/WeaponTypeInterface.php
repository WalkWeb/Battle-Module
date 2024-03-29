<?php

declare(strict_types=1);

namespace Battle\Weapon\Type;

use Battle\Action\ActionCollection;
use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;
use Exception;

/**
 * Каждый тип оружия имеет свои особенности:
 *
 * Мечи, Двуручные мечи - бонус к меткости
 * Топоры, Двуручные топоры - бонус к урону
 * Дробящее, Двуручное дробящее - шанс оглушить цель при критическом ударе. Одноручные булавы оглушают на 1 ход,
 * двуручные на 2, тяжелые двуручные булавы на 3 хода
 * Кинжалы - накладывает кровотечение на 3 хода на 20% от удара
 * Копья - бонус к защите
 * Луки - бонус к скорости атаки
 * Посохи - бонус к получаемой концентрации
 * Жезлы - при критическом ударе снимают один положительный эффект с цели
 * Безоружный бой - бонус к получаемой ярости
 *
 * Тяжелое оружие, Пики и Арбалеты помимо других бонусов имеют:
 * 1. Сниженную скорость атаки
 * 2. Увеличенный урон
 * 3. Сильно увеличенный множитель критического удара
 * 4. Игнорируют блок цели
 *
 * Помимо этого:
 * Тяжелые мечи/топоры/булавы бонусы аналогичны не-тяжелым типам
 * Пики - бонус к защите
 * Арбалеты - бонус к критическому урону
 *
 * Типы оружия в перспективу:
 * Когти - бонус к vampire, т.е. имеют встроенное воровство здоровья при ударе
 * Ритуальные кинжалы - бонус к magic_vampire, т.е. имеют встроенное воровство маны при ударе
 *
 * @package Battle\Weapon\Type
 */
interface WeaponTypeInterface
{
    public const NONE                 = 0; // Отсутствие типа оружия, например в уроне от эффекта
    public const SWORD                = 1;
    public const AXE                  = 2;
    public const MACE                 = 3;
    public const DAGGER               = 4;
    public const SPEAR                = 5;
    public const BOW                  = 6;
    public const STAFF                = 7;
    public const WAND                 = 8;
    public const TWO_HAND_SWORD       = 9;
    public const TWO_HAND_AXE         = 10;
    public const TWO_HAND_MACE        = 11;
    public const HEAVY_TWO_HAND_SWORD = 12;
    public const HEAVY_TWO_HAND_AXE   = 13;
    public const HEAVY_TWO_HAND_MACE  = 14;
    public const LANCE                = 15;
    public const CROSSBOW             = 16;
    public const UNARMED              = 17; // При сражении простыми кулаками

    /**
     * Возвращает ID типа оружия
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Возвращает название типа оружия
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Возвращает коллекцию эффектов (EffectAction), который будет применен к цели в случае критического удара.
     * Например, булавы при критическом ударе оглушают, а кинжалы вызывают кровотечение
     *
     * @param UnitInterface $targetUnit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws Exception
     */
    public function getOnCriticalAction(UnitInterface $targetUnit, CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection;
}
