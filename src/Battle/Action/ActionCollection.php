<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Exception\ActionCollectionException;

class ActionCollection
{
    /**
     * @var ActionInterface[]
     */
    private $actions = [];

    /**
     * @param array $actions
     * @throws ActionCollectionException
     */
    public function __construct(array $actions)
    {
        foreach ($actions as $action) {
            if (!$action instanceof ActionInterface) {
                throw new ActionCollectionException(ActionCollectionException::INCORRECT_ACTION);
            }
            $this->actions[] = $action;
        }
    }

    /**
     * @return ActionInterface[]
     */
    public function getActions(): array
    {
        return $this->actions;
    }
}
