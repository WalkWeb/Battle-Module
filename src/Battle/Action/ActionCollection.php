<?php

declare(strict_types=1);

namespace Battle\Action;

class ActionCollection
{
    // todo CollectionTrait

    /**
     * @var ActionInterface[]
     */
    private $actions = [];

    /**
     * @param array $actions
     * @throws ActionException
     */
    public function __construct(array $actions)
    {
        foreach ($actions as $action) {
            if (!$action instanceof ActionInterface) {
                throw new ActionException(ActionException::INCORRECT_ACTION);
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
