<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Container\ContainerInterface;
use Exception;
use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;

class SummonAction extends AbstractAction
{
    private const HANDLE_METHOD            = 'applySummonAction';
    private const DEFAULT_ANIMATION_METHOD = 'summon';
    private const DEFAULT_MESSAGE_METHOD   = 'summon';

    /**
     * @var string
     */
    private string $name;

    /**
     * @var UnitInterface
     */
    private UnitInterface $summon;

    /**
     * В отличие от прочих событий, SummonAction всегда применяется к себе и не требует $typeTarget в конструктор
     *
     * @param ContainerInterface $container
     * @param UnitInterface $actionUnit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @param string $name
     * @param UnitInterface $summon
     * @param string $icon
     */
    public function __construct(
        ContainerInterface $container,
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        string $name,
        UnitInterface $summon,
        string $icon = ''
    )
    {
        parent::__construct($container, $actionUnit, $enemyCommand, $alliesCommand, self::TARGET_SELF, $icon);
        $this->name = $name;
        $this->summon = $summon;
    }

    public function getHandleMethod(): string
    {
        return self::HANDLE_METHOD;
    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $this->alliesCommand->getUnits()->add($this->summon);
        $this->actionUnit->applyAction($this);
    }

    /**
     * @return string
     */
    public function getNameAction(): string
    {
        return $this->name;
    }

    /**
     * @return UnitInterface
     */
    public function getSummonUnit(): UnitInterface
    {
        return $this->summon;
    }

    public function getAnimationMethod(): string
    {
        return self::DEFAULT_ANIMATION_METHOD;
    }

    public function getMessageMethod(): string
    {
        return self::DEFAULT_MESSAGE_METHOD;
    }

    /**
     * Призыв существ считается всегда возможным - потому что лимита на количество юнитов в группе нет
     *
     * @return bool
     */
    public function canByUsed(): bool
    {
        return true;
    }
}
