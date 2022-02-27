<?php

declare(strict_types=1);

namespace Battle\Unit\Effect;

use Battle\Action\ActionCollection;
use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Action\BuffAction;
use Battle\Unit\UnitInterface;
use Exception;

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
     * Массив данных по Actions, на основе которого будет создаваться ActionCollection для применения к юниту при
     * применении эффекта
     *
     * @var array
     */
    private $onApplyActions;

    /**
     * Массив данных по Actions, на основе которого будет создаваться ActionCollection для применения к юниту при
     * начале нового раунда (при ходе юнита в новом раунде)
     *
     * @var array
     */
    private $onNextRoundActions;

    /**
     * Массив данных по Actions, на основе которого будет создаваться ActionCollection для применения к юниту при
     * истечении эффекта
     *
     * @var array
     */
    private $onDisableActions;

    /**
     * @var ActionFactory
     */
    private $actionFactory;

    public function __construct(
        string $name,
        string $icon,
        int $duration,
        array $onApplyActions,
        array $onNextRoundActions,
        array $onDisableActions,
        ActionFactory $actionFactory = null
    )
    {
        $this->name = $name;
        $this->icon = $icon;
        $this->baseDuration = $duration;
        $this->duration = $duration;
        $this->onApplyActions = $onApplyActions;
        $this->onNextRoundActions = $onNextRoundActions;
        $this->onDisableActions = $onDisableActions;
        $this->actionFactory = $actionFactory ?? new ActionFactory();
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
     * @throws Exception
     */
    public function getOnApplyActions(): ActionCollection
    {
        return $this->createActionCollection($this->onApplyActions);
    }

    /**
     * @return ActionCollection
     * @throws Exception
     */
    public function getOnNextRoundActions(): ActionCollection
    {
        return $this->createActionCollection($this->onNextRoundActions);
    }

    /**
     * @return ActionCollection
     * @throws Exception
     */
    public function getOnDisableActions(): ActionCollection
    {
        $collection = $this->createActionCollection($this->onDisableActions);

        // Также, к коллекции событий необходимо добавить revertAction от бафов, примененных сразу
        foreach ($this->onApplyActions as $applyAction) {
            if ($applyAction instanceof BuffAction) {
                $collection->add($applyAction->getRevertAction());
            }
        }

        return $collection;
    }

    public function newRound(): void
    {
        foreach ($this->onNextRoundActions as $action) {
            $action->clearFactualPower();
        }
    }
    
    public function nextRound(): void
    {
        $this->duration--;
    }

    public function resetDuration(): void
    {
        $this->duration = $this->baseDuration;
    }

    public function changeActionUnit(UnitInterface $unit): void
    {
        foreach ($this->onApplyActions as $onApplyAction) {
            $onApplyAction->changeActionUnit($unit);
        }

        foreach ($this->onNextRoundActions as $onNextRoundAction) {
            $onNextRoundAction->changeActionUnit($unit);
        }

        foreach ($this->onDisableActions as $onDisableAction) {
            $onDisableAction->changeActionUnit($unit);
        }
    }

    /**
     * @param array $data
     * @return ActionCollection
     * @throws Exception
     */
    private function createActionCollection(array $data): ActionCollection
    {
        $collection = new ActionCollection();

        foreach ($data as $datum) {
            $collection->add($this->createAction($datum));
        }

        return $collection;
    }

    /**
     * @param $data
     * @return ActionInterface
     * @throws Exception
     */
    private function createAction($data): ActionInterface
    {
        if (!is_array($data)) {
            throw new EffectException(EffectException::INVALID_ACTION_DATA);
        }

        return $this->actionFactory->create($data);
    }
}
