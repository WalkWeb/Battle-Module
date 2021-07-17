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
     * TODO На удаление? Или на расширение механики вывода результата?
     *
     * @var bool
     */
    private $debug;

    /**
     * @param int $actionCommand
     * @param UnitInterface $actionUnit
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @param ContainerInterface $container
     * @param bool|null $debug
     */
    public function __construct(
        int $actionCommand,
        UnitInterface $actionUnit,
        CommandInterface $leftCommand,
        CommandInterface $rightCommand,
        ContainerInterface $container,
        ?bool $debug = false
    )
    {
        $this->actionCommand = $actionCommand;
        $this->actionUnit = $actionUnit;
        $this->leftCommand = $leftCommand;
        $this->rightCommand = $rightCommand;
        $this->container = $container;
        $this->debug = $debug;

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

        $actionCollection = $this->actionUnit->getAction($enemyCommand, $alliesCommand);

        foreach ($actionCollection as $action) {

            if (!$enemyCommand->isAlive()) {
                break;
            }

            if (!$action->canByUsed()) {
                throw new StrokeException(StrokeException::CANT_BE_USED_ACTION);
            }

            $message = $action->handle();

            $this->container->getStatistic()->addUnitAction($action);
            $this->container->getScenario()->addAction($action, $this->container->getStatistic());
            $this->container->getFullLog()->add('<p>' . $message . '</p>');
            $this->container->getChat()->add($message);
        }

        //--------------------------------------------------------------------------------------------------------------

        $this->actionUnit->madeAction();

        $this->container->getFullLog()->add($view->renderCommandView($this->leftCommand, $this->rightCommand, true));
        $this->container->getFullLog()->add($view->getUnitsStats($this->leftCommand, $this->rightCommand));
    }
}
