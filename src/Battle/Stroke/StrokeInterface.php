<?php

namespace Battle\Stroke;

use Exception;

interface StrokeInterface
{
    /**
     * Совершает ход одного юнита в бою
     *
     * @throws Exception
     */
    public function handle(): void;
}
