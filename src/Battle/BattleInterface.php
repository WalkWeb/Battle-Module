<?php

namespace Battle;

use Battle\Result\ResultInterface;
use Battle\Translation\TranslationInterface;

interface BattleInterface
{
    public const COMMAND_PARAMETER = 'command';
    public const LEFT_COMMAND      = 1;
    public const RIGHT_COMMAND     = 2;

    /**
     * Обрабатывает бой, возвращая результат выполнения
     *
     * @return ResultInterface
     */
    public function handle(): ResultInterface;

    /**
     * Возвращает текущий режим работы боя - в debug режиме или нет
     *
     * @return bool
     */
    public function isDebug(): bool;

    /**
     * Возвращает установленный Translation в модуле боя
     *
     * @return TranslationInterface
     */
    public function getTranslation(): TranslationInterface;
}
