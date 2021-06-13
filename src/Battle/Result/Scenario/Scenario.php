<?php

declare(strict_types=1);

namespace Battle\Result\Scenario;

use Battle\Action\ActionInterface;
use Battle\Action\Damage\DamageAction;
use Battle\Action\Heal\HealAction;
use Battle\Action\Other\WaitAction;
use Battle\Unit\UnitInterface;
use JsonException;

class Scenario implements ScenarioInterface
{
    /**
     * @var array
     */
    private $scenario = [];

    public function addAction(ActionInterface $action): void
    {
        switch ($action) {
            case $action instanceof DamageAction:
                $this->addDamage($action);
                break;
            case $action instanceof HealAction:
                $this->addHeal($action);
                break;
            case $action instanceof WaitAction:
                $this->addWait();
                break;
        }
    }

    private function addDamage(DamageAction $action): void
    {
        $this->scenario[] = [
            'step'    => 1, // todo round
            'attack'  => 1, // todo stroke
            'effects' => [
                [
                    'user_id'        => $action->getActionUnit()->getId(),
                    'class'          => $this->getAttackClass($action->getActionUnit()),
                    'unit_cons_bar2' => $this->getConcentrationBarWidth($action->getActionUnit()),
                    'unit_rage_bar2' => $this->getRageBarWidth($action->getActionUnit()),
                    'unit_effects'   => $this->getUnitEffects(),
                    'targets'        => [
                        [
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

    private function addHeal(HealAction $action): void
    {
        $this->scenario[] = [
            'step'    => 1, // todo round
            'attack'  => 1, // todo stroke
            'effects' => [
                [
                    'user_id'        => $action->getActionUnit()->getId(),
                    'class'          => 'd_buff',
                    'unit_cons_bar2' => $this->getConcentrationBarWidth($action->getActionUnit()),
                    'unit_rage_bar2' => $this->getRageBarWidth($action->getActionUnit()),
                    'targets'        => [
                        [
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

    private function addWait(): void
    {
        $this->scenario[] = [
            'step'    => 1, // todo round
            'attack'  => 1, // todo stroke
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
        // TODO Эффекты пока не реализованы
        return '';
    }
}
