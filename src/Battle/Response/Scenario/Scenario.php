<?php

declare(strict_types=1);

namespace Battle\Response\Scenario;

use Battle\Action\ActionException;
use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Action\EffectAction;
use Battle\Action\HealAction;
use Battle\Action\ManaRestoreAction;
use Battle\Action\ResurrectionAction;
use Battle\Action\SummonAction;
use Battle\Action\WaitAction;
use Battle\Response\Statistic\StatisticInterface;
use Battle\Unit\UnitInterface;
use Exception;
use JsonException;

class Scenario implements ScenarioInterface
{
    /**
     * @var array
     */
    private array $scenario = [];

    /**
     * Добавляет в сценарий анимацию, соответствующую указанному Action
     *
     * @param ActionInterface $action
     * @param StatisticInterface $statistic
     * @throws Exception
     * @uses damage, heal, effectHeal, effectManaRestore, summon, effect, wait, skip, resurrected
     */
    public function addAnimation(ActionInterface $action, StatisticInterface $statistic): void
    {
        $animationMethod = $action->getAnimationMethod();

        if (!method_exists($this, $animationMethod)) {
            throw new ScenarioException(ScenarioException::UNDEFINED_ANIMATION_METHOD . ': ' . $animationMethod);
        }

        $this->$animationMethod($action, $statistic);
    }

    /**
     * @param DamageAction $action
     * @param StatisticInterface $statistic
     * @throws ActionException
     */
    private function damage(DamageAction $action, StatisticInterface $statistic): void
    {
        $targetEffects = [];

        foreach ($action->getTargetUnits() as $targetUnit) {
            if ($action->isBlocked($targetUnit)) {
                $targetEffects[] = $this->createBlockedDamageTargetEffect($targetUnit);
            } elseif ($action->isEvaded($targetUnit)) {
                $targetEffects[] = $this->createDodgedDamageTargetEffect($targetUnit);
            } else {
                $targetEffects[] = $this->createDamageTargetEffect($action, $targetUnit);
            }
        }

        if ($action->getRestoreLifeFromVampirism() > 0) {
            $targetEffects[] = $this->createVampirismEffect($action);
        }

        $this->scenario[] = [
            'step'    => $statistic->getRoundNumber(),
            'attack'  => $statistic->getStrokeNumber(),
            'effects' => [
                [
                    'user_id'        => $action->getActionUnit()->getId(),
                    'class'          => $this->getAttackClass($action->getActionUnit()),
                    'unit_cons_bar2' => $this->getConcentrationBarWidth($action->getActionUnit()),
                    'unit_rage_bar2' => $this->getRageBarWidth($action->getActionUnit()),
                    'unit_effects'   => $this->getUnitEffects($action->getActionUnit()),
                    'targets'        => $targetEffects,
                ],
            ],
        ];
    }

    /**
     * @param DamageAction $action
     * @param StatisticInterface $statistic
     * @throws ActionException
     */
    public function effectDamage(DamageAction $action, StatisticInterface $statistic): void
    {
        $targetEffects = [];

        foreach ($action->getTargetUnits() as $targetUnit) {
            $targetEffects[] = $this->createDamageTargetEffect($action, $targetUnit, true);
        }

        $this->scenario[] = [
            'step'    => $statistic->getRoundNumber(),
            'attack'  => $statistic->getStrokeNumber(),
            'effects' => [
                [
                    'user_id'      => $action->getActionUnit()->getId(),
                    'unit_effects' => $this->getUnitEffects($action->getActionUnit()),
                    'targets'      => $targetEffects,
                ],
            ],
        ];
    }

    /**
     * @param HealAction $action
     * @param StatisticInterface $statistic
     * @throws ActionException
     */
    private function heal(HealAction $action, StatisticInterface $statistic): void
    {
        $targetEffects = [];

        foreach ($action->getTargetUnits() as $targetUnit) {
            $targetEffects[] = [
                'type'              => 'change',
                'user_id'           => $targetUnit->getId(),
                'ava'               => 'unit_ava_green',
                'recdam'            => '+' . $action->getFactualPowerByUnit($targetUnit),
                'hp'                => $this->getLife($targetUnit),
                'thp'               => $this->getTotalLife($targetUnit),
                'hp_bar_class'      => $this->getHpBarClassBackground($targetUnit),
                'hp_bar_class2'     => $this->getHpBarClass($targetUnit),
                'unit_hp_bar_width' => $this->getLifeBarWidth($targetUnit),
                'unit_effects'      => $this->getUnitEffects($targetUnit),
            ];
        }

        $this->scenario[] = [
            'step'    => $statistic->getRoundNumber(),
            'attack'  => $statistic->getStrokeNumber(),
            'effects' => [
                [
                    'user_id'        => $action->getActionUnit()->getId(),
                    'class'          => 'd_buff',
                    'unit_cons_bar2' => $this->getConcentrationBarWidth($action->getActionUnit()),
                    'unit_rage_bar2' => $this->getRageBarWidth($action->getActionUnit()),
                    'targets'        => $targetEffects,
                ],
            ],
        ];
    }

    /**
     * @param HealAction $action
     * @param StatisticInterface $statistic
     * @throws ActionException
     */
    public function effectHeal(HealAction $action, StatisticInterface $statistic): void
    {
        $targetEffects = [];

        foreach ($action->getTargetUnits() as $targetUnit) {
            $targetEffects[] = [
                'type'              => 'change',
                'user_id'           => $action->getActionUnit()->getId(),
                'ava'               => 'unit_ava_green',
                'recdam'            => '+' . $action->getFactualPowerByUnit($targetUnit),
                'hp'                => $this->getLife($targetUnit),
                'thp'               => $this->getTotalLife($targetUnit),
                'unit_hp_bar_width' => $this->getLifeBarWidth($targetUnit),
            ];
        }

        $this->scenario[] = [
            'step'    => $statistic->getRoundNumber(),
            'attack'  => $statistic->getStrokeNumber(),
            'effects' => [
                [
                    'user_id'      => $action->getActionUnit()->getId(),
                    'unit_effects' => $this->getUnitEffects($action->getActionUnit()),
                    'targets'      => $targetEffects,
                ],
            ],
        ];
    }

    /**
     * @param ManaRestoreAction $action
     * @param StatisticInterface $statistic
     * @throws Exception
     */
    private function effectManaRestore(ManaRestoreAction $action, StatisticInterface $statistic): void
    {
        $targetEffects = [];

        foreach ($action->getTargetUnits() as $targetUnit) {
            $targetEffects[] = [
                'type'              => 'change',
                'user_id'           => $action->getActionUnit()->getId(),
                'ava'               => 'unit_ava_blue',
                'recdam'            => '+' . $action->getFactualPowerByUnit($targetUnit),
                'hp'                => $this->getLife($targetUnit),
                'thp'               => $this->getTotalLife($targetUnit),
                'unit_hp_bar_width' => $this->getLifeBarWidth($targetUnit),
            ];
        }

        $this->scenario[] = [
            'step'    => $statistic->getRoundNumber(),
            'attack'  => $statistic->getStrokeNumber(),
            'effects' => [
                [
                    'user_id'      => $action->getActionUnit()->getId(),
                    'unit_effects' => $this->getUnitEffects($action->getActionUnit()),
                    'targets'      => $targetEffects,
                ],
            ],
        ];
    }

    /**
     * @param SummonAction $action
     * @param StatisticInterface $statistic
     */
    private function summon(SummonAction $action, StatisticInterface $statistic): void
    {
        $summon = $action->getSummonUnit();

        $this->scenario[] = [
            'step'    => $statistic->getRoundNumber(),
            'attack'  => $statistic->getStrokeNumber(),
            'effects' => [
                [
                    'user_id'        => $action->getActionUnit()->getId(),
                    'class'          => 'd_buff',
                    'unit_cons_bar2' => $this->getConcentrationBarWidth($action->getActionUnit()),
                    'unit_rage_bar2' => $this->getRageBarWidth($action->getActionUnit()),
                    'targets'        => [
                        [
                            'type'            => 'summon',
                            'summon_row'      => $this->getSummonRow($action),
                            // Summon
                            'id'              => $summon->getId(),
                            'hp_bar_class'    => $this->getHpBarClassBackground($summon),
                            'hp_bar_class2'   => $this->getHpBarClass($summon),
                            'hp_bar_width'    => $this->getLifeBarWidth($summon),
                            'unit_box2_class' => $this->getUnitBoxClass($summon),
                            'hp'              => $this->getLife($summon),
                            'thp'             => $this->getTotalLife($summon),
                            'cons_bar_width'  => $this->getConcentrationBarWidth($summon),
                            'rage_bar_width'  => $this->getRageBarWidth($summon),
                            'avatar'          => $summon->getAvatar(),
                            'name'            => $summon->getName(),
                            'name_color'      => $summon->getRace()->getColor(),
                            'icon'            => $summon->getIcon(),
                            'level'           => $summon->getLevel(),
                            'exist_class'     => (bool)$summon->getClass(),
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param EffectAction $action
     * @param StatisticInterface $statistic
     * @throws ActionException
     */
    private function effect(EffectAction $action, StatisticInterface $statistic): void
    {
        $targetEffects = [];

        foreach ($action->getTargetUnits() as $targetUnit) {
            $targetEffects[] = [
                'type'              => 'change',
                'user_id'           => $targetUnit->getId(),
                'hp'                => $this->getLife($targetUnit),
                'thp'               => $this->getTotalLife($targetUnit),
                'unit_hp_bar_width' => $this->getLifeBarWidth($targetUnit),
                'unit_effects'      => $this->getUnitEffects($targetUnit),
            ];
        }

        $this->scenario[] = [
            'step'    => $statistic->getRoundNumber(),
            'attack'  => $statistic->getStrokeNumber(),
            'effects' => [
                [
                    'user_id'        => $action->getActionUnit()->getId(),
                    'class'          => 'd_buff', // TODO 1) Эффекта на аватаре не происходит, 2) Необходимо разделять эффекты на положительные и отрицательные
                    'hp'             => $this->getLife($action->getActionUnit()),
                    'thp'            => $this->getTotalLife($action->getActionUnit()),
                    'unit_cons_bar2' => $this->getConcentrationBarWidth($action->getActionUnit()),
                    'unit_rage_bar2' => $this->getRageBarWidth($action->getActionUnit()),
                    'unit_effects'   => $this->getUnitEffects($action->getActionUnit()),
                    'targets'        => $targetEffects,
                ],
            ],
        ];
    }

    /**
     * Создает анимацию воскрешения. Пока она аналогична лечению, но в будущем скорее всего изменится - по этому
     * делается сразу отдельным методом
     *
     * @param ResurrectionAction $action
     * @param StatisticInterface $statistic
     * @throws ActionException
     */
    private function resurrected(ResurrectionAction $action, StatisticInterface $statistic): void
    {
        $targetEffects = [];

        foreach ($action->getTargetUnits() as $targetUnit) {
            $targetEffects[] = [
                'type'              => 'change',
                'user_id'           => $targetUnit->getId(),
                'ava'               => 'unit_ava_green',
                'recdam'            => '+' . $action->getFactualPower(),
                'hp'                => $this->getLife($targetUnit),
                'thp'               => $this->getTotalLife($targetUnit),
                'hp_bar_class'      => $this->getHpBarClassBackground($targetUnit),
                'hp_bar_class2'     => $this->getHpBarClass($targetUnit),
                'unit_hp_bar_width' => $this->getLifeBarWidth($targetUnit),
                'avas'              => $this->getAvaClassTarget($targetUnit),
                'unit_effects'      => $this->getUnitEffects($targetUnit),
            ];
        }

        $this->scenario[] = [
            'step'    => $statistic->getRoundNumber(),
            'attack'  => $statistic->getStrokeNumber(),
            'effects' => [
                [
                    'user_id'        => $action->getActionUnit()->getId(),
                    'class'          => 'd_buff',
                    'unit_cons_bar2' => $this->getConcentrationBarWidth($action->getActionUnit()),
                    'unit_rage_bar2' => $this->getRageBarWidth($action->getActionUnit()),
                    'targets'        => $targetEffects,
                ],
            ],
        ];
    }

    /**
     * @param ActionInterface $action
     * @param StatisticInterface $statistic
     * @return WaitAction только для того, чтобы IDE не ругался на то, что $action не используется в методе
     */
    private function wait(ActionInterface $action, StatisticInterface $statistic): ActionInterface
    {
        $this->scenario[] = [
            'step'    => $statistic->getRoundNumber(),
            'attack'  => $statistic->getStrokeNumber(),
            'effects' => [],
        ];

        return $action;
    }

    /**
     * При некоторых событиях не нужно добавлять никаких анимаций
     */
    private function skip(): void
    {
    }

    /**
     * @return string
     * @throws JsonException
     */
    public function getJson(): string
    {
        return json_encode($this->scenario, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return array
     */
    public function getArray(): array
    {
        return $this->scenario;
    }

    /**
     * @param SummonAction $action
     * @return string
     */
    public function getSummonRow(SummonAction $action): string
    {
        if ($action->getActionUnit()->getCommand() === 1 && $action->getSummonUnit()->isMelee()) {
            return 'left_command_melee';
        }
        if ($action->getActionUnit()->getCommand() === 1 && !$action->getSummonUnit()->isMelee()) {
            return 'left_command_range';
        }
        if ($action->getActionUnit()->getCommand() === 2 && $action->getSummonUnit()->isMelee()) {
            return 'right_command_melee';
        }
        return 'right_command_range';
    }

    private function getAttackClass(UnitInterface $unit): string
    {
        return $unit->getCommand() === 1 ? 'd_attack' : 'd_attack_s2';
    }

    private function getLifeBarWidth(UnitInterface $unit): int
    {
        if ($unit->getMana() > 0 && $unit->getDefense()->getMentalBarrier() > 0) {
            return (int)($unit->getMana() / $unit->getTotalMana() * 100);
        }

        return (int)($unit->getLife() / $unit->getTotalLife() * 100);
    }

    private function getConcentrationBarWidth(UnitInterface $unit): int
    {
        return (int)($unit->getConcentration() / UnitInterface::MAX_CONCENTRATION * 100);
    }

    private function getRageBarWidth(UnitInterface $unit): int
    {
        return (int)($unit->getRage() / UnitInterface::MAX_RAGE * 100);
    }

    private function getAvaClassTarget(UnitInterface $unit): string
    {
        return $unit->getLife() > 0 ? 'unit_ava_blank' : 'unit_ava_dead';
    }

    /**
     * Длительность эффекта отображается только в том случае, если она меньше 10. Это сделано для того, чтобы большая
     * цифра длительности не выходила за рамки иконки эффекта
     *
     * @param UnitInterface $unit
     * @return array
     */
    private function getUnitEffects(UnitInterface $unit): array
    {
        $data = [];

        foreach ($unit->getEffects() as $effect) {
            $data[] = [
                'icon'     => $effect->getIcon(),
                'duration' => $effect->getDuration() < 10 ? (string)$effect->getDuration() : '',
            ];
        }

        return $data;
    }

    /**
     * Определяет, какую иконку должен иметь юнит - с полосками ярости и концентрации, или без. Если класса нет - то эти
     * полоски не нужны
     *
     * @param UnitInterface $unit
     * @return string
     */
    private function getUnitBoxClass(UnitInterface $unit): string
    {
        return $unit->getClass() ? 'unit_box2' : 'unit_box2_na';
    }

    /**
     * Создает массив параметров для анимации получения удара у цели
     *
     * @param ActionInterface $action
     * @param UnitInterface $targetUnit
     * @param bool $effect - является ли данный урон уроном от эффекта
     * @return array
     * @throws ActionException
     */
    private function createDamageTargetEffect(ActionInterface $action, UnitInterface $targetUnit, bool $effect = false): array
    {
        return [
            'type'              => 'change',
            'user_id'           => $targetUnit->getId(),
            'hp'                => $this->getLife($targetUnit),
            'thp'               => $this->getTotalLife($targetUnit),
            'hp_bar_class'      => $this->getHpBarClassBackground($targetUnit),
            'hp_bar_class2'     => $this->getHpBarClass($targetUnit),
            'recdam'            => '-' . $action->getFactualPowerByUnit($targetUnit),
            'unit_hp_bar_width' => $this->getLifeBarWidth($targetUnit),
            'unit_cons_bar2'    => $this->getConcentrationBarWidth($targetUnit),
            'unit_rage_bar2'    => $this->getRageBarWidth($targetUnit),
            'ava'               => $effect ? 'unit_ava_effect_damage' : 'unit_ava_red',
            'avas'              => $this->getAvaClassTarget($targetUnit),
            'unit_effects'      => $this->getUnitEffects($targetUnit),
        ];
    }

    /**
     * Создает массив параметров для анимации блока удара у цели
     *
     * @param UnitInterface $targetUnit
     * @return array
     */
    private function createBlockedDamageTargetEffect(UnitInterface $targetUnit): array
    {
        return [
            'type'         => 'change',
            'user_id'      => $targetUnit->getId(),
            'class'        => 'd_block',
            'unit_effects' => $this->getUnitEffects($targetUnit),
        ];
    }

    /**
     * Создает массив параметров для анимации уклонения от удара
     *
     * @param UnitInterface $targetUnit
     * @return array
     */
    private function createDodgedDamageTargetEffect(UnitInterface $targetUnit): array
    {
        return [
            'type'         => 'change',
            'user_id'      => $targetUnit->getId(),
            'class'        => $targetUnit->getCommand() === 1 ? 'd_evasion_s2' : 'd_evasion',
            'unit_effects' => $this->getUnitEffects($targetUnit),
        ];
    }

    /**
     * @param ActionInterface $action
     * @return array
     * @throws ActionException
     */
    private function createVampirismEffect(ActionInterface $action): array
    {
        return [
            'type'              => 'change',
            'user_id'           => $action->getActionUnit()->getId(),
            'ava'               => 'unit_ava_green',
            'recdam'            => '+' . $action->getRestoreLifeFromVampirism(),
            'hp'                => $this->getLife($action->getActionUnit()),
            'thp'               => $this->getTotalLife($action->getActionUnit()),
            'unit_hp_bar_width' => $this->getLifeBarWidth($action->getActionUnit()),
        ];
    }

    // TODO С одной стороны стоит отказаться от Life и заменить на Resource (т.е. это может быть или мана или здоровье)
    // TODO С другой стороны в будущем возможно в интерфейсе будет отдельные полоски здоровья и маны

    /**
     * Возвращает значения "здоровья" на полоске со здоровьем. Если юнит имеет ментальный барьер и ману - то будет
     * отображаться полоска с маной
     *
     * @param UnitInterface $unit
     * @return int
     */
    private function getLife(UnitInterface $unit): int
    {
        if ($unit->getMana() > 0 && $unit->getDefense()->getMentalBarrier() > 0) {
            return $unit->getMana();
        }

        return $unit->getLife();
    }

    /**
     * Возвращает значения "здоровья" на полоске со здоровьем. Если юнит имеет ментальный барьер и ману - то будет
     * отображаться полоска с маной
     *
     * @param UnitInterface $unit
     * @return int
     */
    private function getTotalLife(UnitInterface $unit): int
    {
        if ($unit->getMana() > 0 && $unit->getDefense()->getMentalBarrier() > 0) {
            return $unit->getTotalMana();
        }

        return $unit->getTotalLife();
    }

    /**
     * Возвращает класс для отображения стилей полоски здоровья. Если юнит имеет ману и ментальный щит - будет
     * отображаться синяя полоска, иначе красная
     *
     * @param UnitInterface $unit
     * @return string
     */
    private function getHpBarClass(UnitInterface $unit): string
    {
        if ($unit->getMana() > 0 && $unit->getDefense()->getMentalBarrier() > 0) {
            return 'unit_hp_bar2_mana';
        }

        return 'unit_hp_bar2';
    }

    /**
     * Возвращает класс для отображения стилей фона полоски здоровья. Если юнит имеет ману и ментальный щит - будет
     * отображаться синяя полоска, иначе красная
     *
     * @param UnitInterface $unit
     * @return string
     */
    private function getHpBarClassBackground(UnitInterface $unit): string
    {
        if ($unit->getMana() > 0 && $unit->getDefense()->getMentalBarrier() > 0) {
            return 'unit_hp_bar_mana';
        }

        return 'unit_hp_bar';
    }
}
