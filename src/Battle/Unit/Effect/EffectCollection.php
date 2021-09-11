<?php

declare(strict_types=1);

namespace Battle\Unit\Effect;

use Battle\Action\ActionCollection;
use Battle\Action\ActionException;
use Battle\Traits\CollectionTrait;
use Battle\Unit\UnitInterface;
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
     * Юнит, к которому относится данная коллекция эффектов
     *
     * @var UnitInterface
     */
    private $parentUnit;

    public function __construct(UnitInterface $parentUnit)
    {
        $this->parentUnit = $parentUnit;
    }

    /**
     * @param EffectInterface $effect
     * @return ActionCollection
     */
    public function add(EffectInterface $effect): ActionCollection
    {
        // Теперь эффект (а точнее его Actions) будет срабатывать от лица юнита, на который он наложен
        $effect->changeActionUnit($this->parentUnit);

        $onApplyActionCollection = new ActionCollection();

        if ($this->exist($effect)) {
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
     * @param EffectInterface $effect
     * @return bool
     */
    public function exist(EffectInterface $effect): bool
    {
        return array_key_exists($effect->getName(), $this->elements);
    }

    /**
     * Событие newRound происходит вначале хода юнита в текущем раунде, соответственно нужно применить все события
     * getOnNextRoundActions(), которые имеются у эффектов юнита.
     *
     * @return ActionCollection
     */
    public function newRound(): ActionCollection
    {
        $collection = new ActionCollection();

        foreach ($this->elements as $effect) {
            $collection->addCollection($effect->getOnNextRoundActions());
        }

        return $collection;
    }

    /**
     * Событие nextRound происходит тогда, когда все живые юниты во всех командах походили. Соответственно это окончание
     * текущего раунда, и нужно увеличить длительность всех эффектов, а те эффекты, у которых длительность завершилась -
     * удалить
     *
     * @return ActionCollection
     * @throws ActionException
     */
    public function nextRound(): ActionCollection
    {
        $collection = new ActionCollection();

        foreach ($this->elements as $effect) {
            $effect->nextRound();

            if ($effect->getDuration() < 1) {
                $collection->addCollection($effect->getOnDisableActions());
                unset($this->elements[$effect->getName()]);
            }
        }

        return $collection;
    }
}
