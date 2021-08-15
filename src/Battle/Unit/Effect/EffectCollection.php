<?php

declare(strict_types=1);

namespace Battle\Unit\Effect;

use Battle\Action\ActionCollection;
use Battle\Traits\CollectionTrait;
use Countable;
use Iterator;

class EffectCollection implements Iterator, Countable
{
    use CollectionTrait;

    /**
     * @var EffectInterface[]
     */
    private $elements = [];

    /**
     * @param EffectInterface $effect
     * @return ActionCollection
     */
    public function add(EffectInterface $effect): ActionCollection
    {
        $onApplyActionCollection = new ActionCollection();

        if ($this->exist($effect->getName())) {
            $this->elements[$effect->getName()]->resetDuration();
        } else {
            $this->elements[$effect->getName()] = $effect;
            $onApplyActionCollection->addCollection($effect->getOnApplyActions());
        }

        return $onApplyActionCollection;
    }

    /**
     * @param EffectCollection $effects
     * @return ActionCollection
     */
    public function addCollection(EffectCollection $effects): ActionCollection
    {
        $onApplyActionCollection = new ActionCollection();

        foreach ($effects as $effect) {
            $onApplyActionCollection->addCollection($this->add($effect));
        }

        return $onApplyActionCollection;
    }

    /**
     * @return EffectInterface
     */
    public function current(): EffectInterface
    {
        return current($this->elements);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function exist(string $name): bool
    {
        return array_key_exists($name, $this->elements);
    }

    /**
     * @return ActionCollection
     */
    public function nextRound(): ActionCollection
    {
        $collection = new ActionCollection();

        foreach ($this->elements as $effect) {

            $collection->addCollection($effect->getOnNextRoundActions());

            $effect->nextRound();

            if ($effect->getDuration() < 1) {
                $collection->addCollection($effect->getOnDisableActions());
                unset($this->elements[$effect->getName()]);
            }
        }

        return $collection;
    }
}
