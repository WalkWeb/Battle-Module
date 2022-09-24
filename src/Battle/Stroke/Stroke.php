<?php

declare(strict_types=1);

namespace Battle\Stroke;

use Battle\Action\ActionInterface;
use Battle\Command\CommandInterface;
use Battle\Container\ContainerInterface;
use Battle\Unit\UnitInterface;
use Exception;

class Stroke implements StrokeInterface
{
    /**
     * Команда, которая совершает ход: 1 - leftCommand, 2 - rightCommand
     *
     * @var int
     */
    private int $actionCommand;

    /**
     * Юнит совершающий действие
     *
     * @var UnitInterface
     */
    private UnitInterface $actionUnit;

    /**
     * Левая команда
     *
     * @var CommandInterface
     */
    private CommandInterface $leftCommand;

    /**
     * Правая команда
     *
     * @var CommandInterface
     */
    private CommandInterface $rightCommand;

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * @param int $actionCommand
     * @param UnitInterface $actionUnit
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @param ContainerInterface $container
     */
    public function __construct(
        int $actionCommand,
        UnitInterface $actionUnit,
        CommandInterface $leftCommand,
        CommandInterface $rightCommand,
        ContainerInterface $container
    )
    {
        $this->actionCommand = $actionCommand;
        $this->actionUnit = $actionUnit;
        $this->leftCommand = $leftCommand;
        $this->rightCommand = $rightCommand;
        $this->container = $container;
    }

    /**
     * Совершает ход одного юнита в бою
     *
     * @throws Exception
     */
    public function handle(): void
    {
        $view = $this->container->getViewFactory()->create();
        $this->container->getFullLog()->add($view->getUnitsStats($this->leftCommand, $this->rightCommand));

        //--------------------------------------------------------------------------------------------------------------

        $enemyCommand = $this->actionCommand === 1 ? $this->rightCommand : $this->leftCommand;
        $alliesCommand = $this->actionCommand === 1 ? $this->leftCommand : $this->rightCommand;

        $this->handleBeforeActionUnit();
        $this->handleUnitActions($enemyCommand, $alliesCommand);
        $this->handleAfterActionUnit();
        $this->handleDeadActions($enemyCommand, $alliesCommand);

        //--------------------------------------------------------------------------------------------------------------

        $this->actionUnit->madeAction();

        $this->container->getFullLog()->add($view->renderCommandView($this->leftCommand, $this->rightCommand, true));
        $this->container->getFullLog()->add($view->getUnitsStats($this->leftCommand, $this->rightCommand));
    }

    /**
     * Перед тем как юнит начинает ходить - нужно выполнить события эффектов на данном юните, которые срабатывают на
     * каждом раунде
     *
     * Важно: события выполняются только если они могут выполниться. Например, на юните имеется эффект постепенного
     * лечения, но юнит уже здоров - событие лечения от эффекта примениться не сможет, и это нормально
     *
     * @throws Exception
     */
    private function handleBeforeActionUnit(): void
    {
        // TODO Переадресация вызова $unit->getBeforeActions()->getOnNewRoundActions() - нужно отрефакторить
        // $effects->newRound()
        foreach ($this->actionUnit->getBeforeActions() as $action) {
            if ($action->canByUsed()) {
                $this->runAction($action);
            }

            // Если юнит умер после применении эффекта - дальнейшие эффекты применять не нужно
            if (!$this->actionUnit->isAlive()) {
                break;
            }
        }
    }

    /**
     * Выполняет действия юнита, который ходит
     *
     * При этом действия выполняются только если он жив (а он мог умереть от эффектов) и не ходил (некоторые эффекты
     * обездвиживают юнита, отмечая его, как "уже походил")
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @throws Exception
     */
    private function handleUnitActions(CommandInterface $enemyCommand, CommandInterface $alliesCommand): void
    {
        if ($this->actionUnit->isAlive() && !$this->actionUnit->isAction()) {
            foreach ($this->actionUnit->getActions($enemyCommand, $alliesCommand) as $action) {

                if (!$enemyCommand->isAlive()) {
                    break;
                }

                if (!$action->canByUsed()) {
                    throw new StrokeException(
                        StrokeException::CANT_BE_USED_ACTION .
                        '. Action unit: ' . $this->actionUnit->getName() .
                        '. Action: ' . $action->getNameAction()
                    );
                }

                $this->runAction($action);
            }
        }
    }

    /**
     * После того как юнит походил, необходимо выполнить события связанные с окончанием действием эффектов
     *
     * Важно: события выполняются только если они могут выполниться. Например, на юните имеется эффект лечения при
     * завершении эффекта, но юнит уже здоров - событие лечения от эффекта примениться не сможет, и это нормально
     *
     * @throws Exception
     */
    public function handleAfterActionUnit(): void
    {
        foreach ($this->actionUnit->getAfterActions() as $action) {
            if ($action->canByUsed()) {
                $this->runAction($action);
            }
        }
    }

    /**
     * Некоторые способности активируются только после смерти юнита
     *
     * Их реализацию можно сделать более абстрактным способом, когда юнит получая и обрабатывая событие возвращает
     * коллекцию событий в ответ (например, для нанесения урона от рефлекта), но эта реализация затронет большое
     * количество изменений и пока избыточна - просто отдельным методом запрашиваем через отдельный метод способности
     * при смерти, если они есть и готовы к использованию
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @throws Exception
     */
    private function handleDeadActions(CommandInterface $enemyCommand, CommandInterface $alliesCommand): void
    {
        foreach ($enemyCommand->getUnits() as $unit) {

            if (!$unit->isAlive()) {

                $abilities = $unit->getDeadAbilities();

                foreach ($abilities as $ability) {

                    $ability->usage();

                    $actions = $ability->getActions($alliesCommand, $enemyCommand);

                    foreach ($actions as $action) {
                        if ($action->canByUsed()) {
                            $this->runAction($action);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param ActionInterface $action
     * @throws Exception
     */
    private function runAction(ActionInterface $action): void
    {
        $action->handle();

        $message = $this->container->getChat()->addMessage($action);

        $this->container->getStatistic()->addUnitAction($action);
        $this->container->getScenario()->addAnimation($action, $this->container->getStatistic());
        $this->container->getFullLog()->addText($message);
    }
}
