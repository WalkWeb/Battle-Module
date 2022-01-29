<?php

declare(strict_types=1);

namespace Battle\Stroke;

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
    private $actionCommand;

    /**
     * Юнит совершающий действие
     *
     * @var UnitInterface
     */
    private $actionUnit;

    /**
     * Левая команда
     *
     * @var CommandInterface
     */
    private $leftCommand;

    /**
     * Правая команда
     *
     * @var CommandInterface
     */
    private $rightCommand;

    /**
     * @var ContainerInterface
     */
    private $container;

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
        $view = $this->container->getViewFactory()->create($this->container->getTranslation());
        $this->container->getFullLog()->add($view->getUnitsStats($this->leftCommand, $this->rightCommand));

        //--------------------------------------------------------------------------------------------------------------

        $enemyCommand = $this->actionCommand === 1 ? $this->rightCommand : $this->leftCommand;
        $alliesCommand = $this->actionCommand === 1 ? $this->leftCommand : $this->rightCommand;

        $this->handleUnitEffects();
        $this->handleUnitActions($enemyCommand, $alliesCommand);

        // В будущем здесь также будут выполняться события при рефлекте урона и события при смерти юнита

        //--------------------------------------------------------------------------------------------------------------

        $this->actionUnit->madeAction();

        $this->container->getFullLog()->add($view->renderCommandView($this->leftCommand, $this->rightCommand, true));
        $this->container->getFullLog()->add($view->getUnitsStats($this->leftCommand, $this->rightCommand));
    }

    /**
     * Перед тем как юнит начинает ходить - нужно выполнить события эффектов на данном юните, которые срабатывают на
     * каждом раунде
     *
     * @throws Exception
     */
    private function handleUnitEffects(): void
    {
        foreach ($this->actionUnit->getOnNewRoundActions() as $action) {
            if ($action->canByUsed()) {

                $action->handle();

                $message = $this->container->getChat()->addMessage($action);
                $this->container->getStatistic()->addUnitAction($action);
                $this->container->getScenario()->addAnimation($action, $this->container->getStatistic());
                $this->container->getFullLog()->addText($message);
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
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @throws Exception
     */
    private function handleUnitActions(CommandInterface $enemyCommand, CommandInterface $alliesCommand): void
    {
        if ($this->actionUnit->isAlive()) {
            foreach ($this->actionUnit->getAction($enemyCommand, $alliesCommand) as $action) {

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

                $action->handle();

                $message = $this->container->getChat()->addMessage($action);

                $this->container->getStatistic()->addUnitAction($action);
                $this->container->getScenario()->addAnimation($action, $this->container->getStatistic());
                $this->container->getFullLog()->addText($message);
            }
        }
    }
}
