<?php

declare(strict_types=1);

namespace Battle\Result\Scenario;

use Battle\Action\ActionException;
use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Action\EffectAction;
use Battle\Action\HealAction;
use Battle\Action\ResurrectionAction;
use Battle\Action\SummonAction;
use Battle\Action\WaitAction;
use Battle\Result\Statistic\StatisticInterface;
use Battle\Unit\UnitInterface;
use Exception;
use JsonException;

class Scenario implements ScenarioInterface
{
    /**
     * @var array
     */
    private $scenario = [];

    /**
     * Добавляет в сценарий анимацию, соответствующую указанному Action
     *
     * @param ActionInterface $action
     * @param StatisticInterface $statistic
     * @throws Exception
     * @uses damage, heal, effectHeal, summon, effect, wait, skip, resurrected
     */
    public function addAnimation(ActionInterface $action, StatisticInterface $statistic): void
    {
        $animationMethod = $action->getAnimationMethod();

        if (!method_exists($this, $animationMethod)) {
            throw new ScenarioException(ScenarioException::UNDEFINED_ANIMATION_METHOD);
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
            } else {
                $targetEffects[] = $this->createDamageTargetEffect($action, $targetUnit);
            }
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
            $targetEffects[] = [
                // TODO Привести массив параметров в соответствие с createDamageTargetEffect()
                'type'              => 'change',
                'user_id'           => $targetUnit->getId(),
                'ava'               => 'unit_ava_effect_damage',
                'recdam'            => '-' . $action->getFactualPowerByUnit($targetUnit->getId()),
                'hp'                => $targetUnit->getLife(),
                'thp'               => $targetUnit->getTotalLife(),
                'unit_hp_bar_width' => $this->getLifeBarWidth($targetUnit),
                'avas'              => $this->getAvaClassTarget($targetUnit),
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
                'recdam'            => '+' . $action->getFactualPowerByUnit($targetUnit->getId()),
                'hp'                => $targetUnit->getLife(),
                'thp'               => $targetUnit->getTotalLife(),
                'hp_bar_class'      => 'unit_hp_bar',
                'hp_bar_class2'     => 'unit_hp_bar2',
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
                'recdam'            => '+' . $action->getFactualPowerByUnit($targetUnit->getId()),
                'hp'                => $targetUnit->getLife(),
                'thp'               => $targetUnit->getTotalLife(),
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
                            'id'              => $action->getSummonUnit()->getId(),
                            'hp_bar_class'    => 'unit_hp_bar',
                            'hp_bar_class2'   => 'unit_hp_bar2',
                            'hp_bar_width'    => $this->getLifeBarWidth($action->getSummonUnit()),
                            'unit_box2_class' => $this->getUnitBoxClass($action->getSummonUnit()),
                            'hp'              => $action->getSummonUnit()->getLife(),
                            'thp'             => $action->getSummonUnit()->getTotalLife(),
                            'cons_bar_width'  => $this->getConcentrationBarWidth($action->getSummonUnit()),
                            'rage_bar_width'  => $this->getRageBarWidth($action->getSummonUnit()),
                            'avatar'          => $action->getSummonUnit()->getAvatar(),
                            'name'            => $action->getSummonUnit()->getName(),
                            'name_color'      => $action->getSummonUnit()->getRace()->getColor(),
                            'icon'            => $action->getSummonUnit()->getIcon(),
                            'level'           => $action->getSummonUnit()->getLevel(),
                            'exist_class'     => (bool)$action->getSummonUnit()->getClass(),
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
                'type'         => 'change',
                'user_id'      => $targetUnit->getId(),
                'hp'           => $targetUnit->getLife(),
                'thp'          => $targetUnit->getTotalLife(),
                'unit_effects' => $this->getUnitEffects($targetUnit),
            ];
        }

        $this->scenario[] = [
            'step'    => $statistic->getRoundNumber(),
            'attack'  => $statistic->getStrokeNumber(),
            'effects' => [
                [
                    'user_id'        => $action->getActionUnit()->getId(),
                    'class'          => 'd_buff', // TODO 1) Эффекта на аватаре не происходит, 2) Необходимо разделять эффекты на положительные и отрицательные
                    'hp'             => $action->getActionUnit()->getLife(),
                    'thp'            => $action->getActionUnit()->getTotalLife(),
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
                'hp'                => $targetUnit->getLife(),
                'thp'               => $targetUnit->getTotalLife(),
                'hp_bar_class'      => 'unit_hp_bar',
                'hp_bar_class2'     => 'unit_hp_bar2',
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
     * @param WaitAction $action
     * @param StatisticInterface $statistic
     * @return WaitAction только для того, чтобы IDE не ругался на то, что $action не используется в методе
     */
    private function wait(WaitAction $action, StatisticInterface $statistic): WaitAction
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
        return (int)($unit->getLife() / $unit->getTotalLife() * 100);
    }

    private function getConcentrationBarWidth(UnitInterface $unit): int
    {
        return (int)($unit->getConcentration() / UnitInterface::MAX_CONS * 100);
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
     * @return array
     * @throws ActionException
     */
    private function createDamageTargetEffect(ActionInterface $action, UnitInterface $targetUnit): array
    {
        return [
            'type'              => 'change',
            'user_id'           => $targetUnit->getId(),
            'class'             => 'd_red',
            'hp'                => $targetUnit->getLife(),
            'thp'               => $targetUnit->getTotalLife(),
            'hp_bar_class'      => 'unit_hp_bar',
            'hp_bar_class2'     => 'unit_hp_bar2',
            'recdam'            => '-' . $action->getFactualPowerByUnit($targetUnit->getId()),
            'unit_hp_bar_width' => $this->getLifeBarWidth($targetUnit),
            'unit_cons_bar2'    => $this->getConcentrationBarWidth($targetUnit),
            'unit_rage_bar2'    => $this->getRageBarWidth($targetUnit),
            'ava'               => 'unit_ava_red',
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
            'type'    => 'change',
            'user_id' => $targetUnit->getId(),
            'class'   => 'd_block',
        ];
    }
}
