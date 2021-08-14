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
     * TODO При добавлении эффекта можно сразу возвращать коллекцию событий эффекта, при getOnApplyActions()
     *
     * @param EffectInterface $effect
     */
    public function add(EffectInterface $effect): void
    {
        if ($this->exist($effect->getName())) {
            $this->elements[$effect->getName()]->resetDuration();
        } else {
            $this->elements[$effect->getName()] = $effect;
        }
    }

    /**
     * @param EffectCollection $effects
     */
    public function addCollection(EffectCollection $effects): void
    {
        foreach ($effects as $effect) {
            $this->add($effect);
        }
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
