<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Effect;

use Battle\Action\ActionCollection;
use Battle\Unit\Effect\Effect;
use PHPUnit\Framework\TestCase;

class EffectTest extends TestCase
{
    public function testEffectCreate(): void
    {
        $name = 'Effect Name';
        $icon = 'path_to_icon.png';
        $duration = 10;
        $onApplyActions = new ActionCollection();
        $onNextRoundActions = new ActionCollection();
        $onDisableActions = new ActionCollection();

        $effect = new Effect($name, $icon, $duration, $onApplyActions, $onNextRoundActions, $onDisableActions);

        self::assertEquals($name, $effect->getName());
        self::assertEquals($icon, $effect->getIcon());
        self::assertEquals($duration, $effect->getDuration());
        self::assertEquals($duration, $effect->getBaseDuration());
        self::assertEquals($onApplyActions, $effect->getOnApplyActions());
        self::assertEquals($onNextRoundActions, $effect->getOnNextRoundActions());
        self::assertEquals($onDisableActions, $effect->getOnDisableActions());

        $effect->nextRound();
        $effect->nextRound();
        $effect->nextRound();

        self::assertEquals($duration - 3, $effect->getDuration());

        $effect->resetDuration();

        self::assertEquals($duration, $effect->getDuration());
    }
}
