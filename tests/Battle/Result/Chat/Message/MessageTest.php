<?php

declare(strict_types=1);

namespace Tests\Battle\Result\Chat\Message;

use Battle\Action\DamageAction;
use Battle\Container\Container;
use Battle\Result\Chat\Message\Message;
use Battle\Result\Chat\Message\MessageException;
use Battle\Translation\Translation;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\BaseFactory;

// TODO Реализовать полноценные тесты на Message в одном классе. Сейчас они сделаны частями в разных тестах.

class MessageTest extends TestCase
{
    private const EFFECT_DAMAGE_EN = '<span style="color: #1e72e3">unit_1</span> received damage on 10 life from effect Poison';
    private const EFFECT_DAMAGE_RU = '<span style="color: #1e72e3">unit_1</span> получил урон на 10 здоровья от эффекта Отравление';

    /**
     * @throws Exception
     */
    public function testMessageUndefinedMethod(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $message = new Message();

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            30,
            'test attack',
            null,
            'undefinedMessageMethod'
        );

        $this->expectException(MessageException::class);
        $this->expectExceptionMessage(MessageException::UNDEFINED_MESSAGE_METHOD);
        $message->createMessage($action);
    }

    /**
     * Тест на формирование сообщения об уроне от эффекта, на английском
     *
     * @throws Exception
     */
    public function testMessageEffectDamageEn(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $message = new Message();

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            10,
            'Poison',
            null,
            DamageAction::EFFECT_MESSAGE_METHOD
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::EFFECT_DAMAGE_EN, $message->createMessage($action));
    }

    /**
     * Тест на формирование сообщения об уроне от эффекта, на русском
     *
     * @throws Exception
     */
    public function testMessageEffectDamageRu(): void
    {
        $translation = new Translation('ru');
        $message = new Message($translation);
        $container = new Container();
        $container->set(Message::class, $message);
        $container->set(Translation::class, $translation);

//        var_dump($container);
//
//        echo "\n----------------------->\n";
//        echo $translation->getLanguage() . PHP_EOL;
//        echo $container->getTranslation()->getLanguage() . PHP_EOL;
//        echo "----------------------->\n";

        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2, $container);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            10,
            'Poison',
            null,
            DamageAction::EFFECT_MESSAGE_METHOD
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertEquals(self::EFFECT_DAMAGE_RU, $container->getMessage()->createMessage($action));
    }
}
