<?php

declare(strict_types=1);

namespace Battle\Unit\Effect;

use Battle\Action\ActionCollection;
use Battle\Action\ActionException;

class Effect implements EffectInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $icon;

    /**
     * @var int
     */
    private $baseDuration;

    /**
     * @var int
     */
    private $duration;

    /**
     * @var ActionCollection
     */
    private $onApplyActions;

    /**
     * @var ActionCollection
     */
    private $onNextRoundActions;

    /**
     * @var ActionCollection
     */
    private $onDisableActions;

    public function __construct(
        string $name,
        string $icon,
        int $duration,
        ActionCollection $onApplyActions,
        ActionCollection $onNextRoundActions,
        ActionCollection $onDisableActions
    )
    {
        $this->name = $name;
        $this->icon = $icon;
        $this->baseDuration = $duration;
        $this->duration = $duration;
        $this->onApplyActions = $onApplyActions;
        $this->onNextRoundActions = $onNextRoundActions;
        $this->onDisableActions = $onDisableActions;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @return int
     */
    public function getBaseDuration(): int
    {
        return $this->baseDuration;
    }

    /**
     * @return int
     */
    public function getDuration(): int
    {
        return $this->duration;
    }

    /**
     * @return ActionCollection
     */
    public function getOnApplyActions(): ActionCollection
    {
        return $this->onApplyActions;
    }

    /**
     * @return ActionCollection
     */
    public function getOnNextRoundActions(): ActionCollection
    {
        return $this->onNextRoundActions;
    }

    /**
     * @return ActionCollection
     * @throws ActionException
     */
    public function getOnDisableActions(): ActionCollection
    {
        $collection = $this->onDisableActions;

        // Также, к коллекции событий необходимо добавить revertAction от событий, примененных сразу
        foreach ($this->onApplyActions as $applyAction) {
            $collection->add($applyAction->getRevertAction());
        }

        return $collection;
    }

    public function nextRound(): void
    {
        $this->duration--;
    }

    public function resetDuration(): void
    {
        $this->duration = $this->baseDuration;
    }
}
