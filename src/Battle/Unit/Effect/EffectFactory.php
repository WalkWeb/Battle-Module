<?php

declare(strict_types=1);

namespace Battle\Unit\Effect;

use Battle\Action\ActionCollection;
use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Traits\ValidationTrait;
use Exception;

class EffectFactory
{
    use ValidationTrait;

    /**
     * @var ActionFactory
     */
    private $actionFactory;

    public function __construct(ActionFactory $actionFactory)
    {
        $this->actionFactory = $actionFactory;
    }

    /**
     * Создает Effect на основе массива параметров
     *
     * @param array $data
     * @return EffectInterface
     * @throws Exception
     */
    public function create(array $data): EffectInterface
    {
        $name = self::string($data, 'name', EffectException::INVALID_NAME);
        $icon = self::string($data, 'icon', EffectException::INVALID_ICON);
        $duration = self::int($data, 'duration', EffectException::INVALID_DURATION);

        $onApplyActionsData = self::array($data, 'on_apply_actions', EffectException::INVALID_ON_APPLY);
        $onNextRoundActionsData = self::array($data, 'on_next_round_actions', EffectException::INVALID_ON_NEXT_ROUND);
        $onDisableActionsData = self::array($data, 'on_disable_actions', EffectException::INVALID_ON_DISABLE);

        if ((count($onApplyActionsData) + count($onNextRoundActionsData) + count($onDisableActionsData)) === 0) {
            throw new EffectException(EffectException::ZERO_ACTION);
        }

        // Цель создания коллекций - сразу проверить, что в данных нет ошибки
        // В сам эффект будут переданы массивы данных
        $this->createActionCollection($onApplyActionsData);
        $this->createActionCollection($onNextRoundActionsData);
        $this->createActionCollection($onDisableActionsData);

        return new Effect(
            $name,
            $icon,
            $duration,
            $onApplyActionsData,
            $onNextRoundActionsData,
            $onDisableActionsData
        );
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
