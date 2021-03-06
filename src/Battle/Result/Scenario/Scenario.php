<?php

declare(strict_types=1);

namespace Battle\Result\Scenario;

use Battle\Action\ActionException;
use Battle\Action\ActionInterface;
use Battle\Action\Damage\DamageAction;
use Battle\Action\Heal\HealAction;
use Battle\Action\Other\WaitAction;
use Battle\Result\Statistic\StatisticInterface;
use Battle\Action\Summon\SummonAction;
use Battle\Unit\UnitInterface;
use JsonException;

class Scenario implements ScenarioInterface
{
    /**
     * @var array
     */
    private $scenario = [];

    /**
     * @param ActionInterface $action
     * @param StatisticInterface $statistic
     * @throws ActionException
     */
    public function addAction(ActionInterface $action, StatisticInterface $statistic): void
    {
        switch ($action) {
            case $action instanceof DamageAction:
                $this->addDamage($action, $statistic);
                break;
            case $action instanceof HealAction:
                $this->addHeal($action, $statistic);
                break;
            case $action instanceof SummonAction:
                $this->addSummon($action, $statistic);
                break;
            case $action instanceof WaitAction:
                $this->addWait($statistic);
                break;
        }
    }

    /**
     * @param DamageAction $action
     * @param StatisticInterface $statistic
     * @throws ActionException
     */
    private function addDamage(DamageAction $action, StatisticInterface $statistic): void
    {
        $this->scenario[] = [
            'step'    => $statistic->getRoundNumber(),
            'attack'  => $statistic->getStrokeNumber(),
            'effects' => [
                [
                    'user_id'        => $action->getActionUnit()->getId(),
                    'class'          => $this->getAttackClass($action->getActionUnit()),
                    'unit_cons_bar2' => $this->getConcentrationBarWidth($action->getActionUnit()),
                    'unit_rage_bar2' => $this->getRageBarWidth($action->getActionUnit()),
                    'unit_effects'   => $this->getUnitEffects(),
                    'targets'        => [
                        [
                            'type'              => 'change',
                            'user_id'           => $action->getTargetUnit()->getId(),
                            'class'             => 'd_red',
                            'hp'                => $action->getTargetUnit()->getLife(),
                            'thp'               => $action->getTargetUnit()->getTotalLife(),
                            'hp_bar_class'      => 'unit_hp_bar',
                            'hp_bar_class2'     => 'unit_hp_bar2',
                            'recdam'            => '-' . $action->getFactualPower(),
                            'unit_hp_bar_width' => $this->getLifeBarWidth($action->getTargetUnit()),
                            'unit_cons_bar2'    => $this->getConcentrationBarWidth($action->getTargetUnit()),
                            'unit_rage_bar2'    => $this->getRageBarWidth($action->getTargetUnit()),
                            'ava'               => 'unit_ava_red',
                            'avas'              => $this->getAvaClassTarget($action->getTargetUnit()),
                            'unit_effects'      => $this->getUnitEffects(),
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param HealAction $action
     * @param StatisticInterface $statistic
     * @throws ActionException
     */
    private function addHeal(HealAction $action, StatisticInterface $statistic): void
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
                            'type'              => 'change',
                            'user_id'           => $action->getTargetUnit()->getId(),
                            'ava'               => 'unit_ava_green',
                            'recdam'            => '+' . $action->getFactualPower(),
                            'hp'                => $action->getTargetUnit()->getLife(),
                            'thp'               => $action->getTargetUnit()->getTotalLife(),
                            'hp_bar_class'      => 'unit_hp_bar',
                            'hp_bar_class2'     => 'unit_hp_bar2',
                            'unit_hp_bar_width' => $this->getLifeBarWidth($action->getTargetUnit()),
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param SummonAction $action
     * @param StatisticInterface $statistic
     */
    private function addSummon(SummonAction $action, StatisticInterface $statistic): void
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
                            'unit_box2_class' => 'unit_box2', // todo
                            'hp'              => $action->getSummonUnit()->getLife(),
                            'thp'             => $action->getSummonUnit()->getTotalLife(),
                            'cons_bar_width'  => $this->getConcentrationBarWidth($action->getSummonUnit()),
                            'rage_bar_width'  => $this->getRageBarWidth($action->getSummonUnit()),
                            'avatar'          => $action->getSummonUnit()->getAvatar(),
                            'name'            => $action->getSummonUnit()->getName(),
                            'name_color'      => $action->getSummonUnit()->getRace()->getColor(),
                            'icon'            => $action->getSummonUnit()->getIcon(),
                            'level'           => $action->getSummonUnit()->getLevel(),
                        ],
                    ],
                ],
            ],
        ];
    }

    private function addWait(StatisticInterface $statistic): void
    {
        $this->scenario[] = [
            'step'    => $statistic->getRoundNumber(),
            'attack'  => $statistic->getStrokeNumber(),
            'effects' => [],
        ];
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
     * @throws ScenarioException
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

    private function getUnitEffects(): string
    {
        // TODO ?????????????? ???????? ???? ??????????????????????
        return '';
    }
}
