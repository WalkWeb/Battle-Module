<?php

declare(strict_types=1);

namespace Battle\Weapon\Type;

use Battle\Action\ActionCollection;
use Battle\Action\ActionInterface;
use Battle\Command\CommandInterface;
use Battle\Container\ContainerInterface;
use Battle\Traits\AbilityDataTrait;
use Battle\Unit\UnitInterface;
use Exception;

class WeaponType implements WeaponTypeInterface
{
    use AbilityDataTrait;

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

    private static array $onCriticalActions = [
        self::MACE => [
            [
                'type'           => ActionInterface::EFFECT,
                'type_target'    => ActionInterface::TARGET_SELF,
                'name'           => 'Stun',
                'icon'           => '/images/icons/ability/435.png',
                'message_method' => ActionInterface::SKIP_MESSAGE_METHOD,
                'effect'         => [
                    'name'                  => 'Stun',
                    'icon'                  => '/images/icons/ability/435.png',
                    'duration'              => 1,
                    'on_apply_actions'      => [],
                    'on_next_round_actions' => [
                        [
                            'type'             => ActionInterface::PARALYSIS,
                            'type_target'      => ActionInterface::TARGET_SELF,
                            'name'             => 'Stun',
                            'can_be_avoided'   => false,
                            'animation_method' => ActionInterface::SKIP_ANIMATION_METHOD,
                            'message_method'   => ActionInterface::SKIP_MESSAGE_METHOD,
                            'icon'             => '/images/icons/ability/435.png',
                        ],
                    ],
                    'on_disable_actions'    => [],
                ],
            ],
        ],
    ];

    private int $id;
    private string $name;
    private ContainerInterface $container;

    /**
     * @param int $id
     * @param ContainerInterface $container
     * @throws WeaponTypeException
     */
    public function __construct(int $id, ContainerInterface $container)
    {
        $this->id = $id;
        $this->setName($id);
        $this->container = $container;
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
     * @param UnitInterface $parentUnit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws Exception
     */
    public function getOnCriticalAction(UnitInterface $parentUnit, CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        if (!array_key_exists($this->id, self::$onCriticalActions)) {
            return new ActionCollection();
        }

        $actions = new ActionCollection();
        foreach (self::$onCriticalActions[$this->id] as &$actionData) {
            $this->addParameters($actionData, $parentUnit, $enemyCommand, $alliesCommand);
            $actions->add($this->container->getActionFactory()->create($actionData));
        }

        return $actions;
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
