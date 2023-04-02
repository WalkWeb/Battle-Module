<?php

declare(strict_types=1);

namespace Battle\Response\Scenario;

use Exception;

class ScenarioException extends Exception
{
    public const UNDEFINED_ANIMATION_METHOD = 'Scenario: Undefined animation method';
}
