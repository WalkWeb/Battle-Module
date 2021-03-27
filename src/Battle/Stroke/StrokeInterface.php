<?php

namespace Battle\Stroke;

interface StrokeInterface
{
    /**
     * Совершает ход одного юнита в бою
     */
    public function handle(): void;
}
