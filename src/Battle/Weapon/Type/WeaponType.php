<?php

declare(strict_types=1);

namespace Battle\Weapon\Type;

use Battle\Action\ActionCollection;
use Battle\Command\CommandInterface;

class WeaponType implements WeaponTypeInterface
{
    private static array $map = [
        self::NONE                 => 'None',
        self::SWORD                => 'Sword',
        self::AXE                  => 'Axe',
        self::MACE                 => 'Mace',
        self::DAGGER               => 'Dagger',
        self::SPEAR                => 'Spear',
        self::BOW                  => 'Bow',
        self::STAFF                => 'Staff',
        self::WAND                 => 'Wand',
        self::TWO_HAND_SWORD       => 'Two hand sword',
        self::TWO_HAND_AXE         => 'Two hand axe',
        self::TWO_HAND_MACE        => 'Two hand mace',
        self::HEAVY_TWO_HAND_SWORD => 'Heavy two hand sword',
        self::HEAVY_TWO_HAND_AXE   => 'Heavy two hand axe',
        self::HEAVY_TWO_HAND_MACE  => 'Heavy two hand mace',
        self::LANCE                => 'Lance',
        self::CROSSBOW             => 'Crossbow',
        self::UNARMED              => 'Unarmed',
    ];

    private int $id;
    private string $name;

    /**
     * @param int $id
     * @throws WeaponTypeException
     */
    public function __construct(int $id)
    {
        $this->id = $id;
        $this->setName($id);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     */
    public function getActions(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        // TODO
        return new ActionCollection();
    }

    /**
     * @param int $id
     * @throws WeaponTypeException
     */
    private function setName(int $id): void
    {
        if (!array_key_exists($id, self::$map)) {
            throw new WeaponTypeException(WeaponTypeException::UNKNOWN_WEAPON_TYPE_ID . ': ' . $id);
        }

        $this->name = self::$map[$id];
    }
}
