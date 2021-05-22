<?php

declare(strict_types=1);

namespace Tests\Battle\Translation;

use Battle\Translation\Translation;
use Battle\Translation\TranslationException;
use PHPUnit\Framework\TestCase;

class TranslationTest extends TestCase
{
    /**
     * Тест на стандартный успешный перевод сообщения
     *
     * @throws TranslationException
     */
    public function testTranslationTransSuccess(): void
    {
        $language = 'ru';
        $message = 'Battle end';

        $translation = new Translation($language);
        $trans = $translation->trans($message);

        self::assertEquals($this->getMessages($language)[$message], $trans);
    }

    /**
     * Тест ситуации, когда указанного сообщения нет в справочнике по переводу, и вернулось это же сообщение
     *
     * @throws TranslationException
     */
    public function testTranslationTransUndefinedMessage(): void
    {
        $language = 'ru';
        $message = 'Battle end xxxxxx';

        $translation = new Translation($language);
        $trans = $translation->trans($message);

        self::assertEquals($message, $trans);
    }

    /**
     * Тест ситуации, когда указан неизвестный язык, и возвращен вариант языка по умолчанию
     *
     * @throws TranslationException
     */
    public function testTranslationTransUndefinedLanguage(): void
    {
        $language = 'xx';
        $message = 'Battle end';

        $translation = new Translation($language);
        $trans = $translation->trans($message);

        self::assertEquals($message, $trans);
    }

    /**
     * Тест ситуации, когда справочник переводов составлен некорректно, и вместо строки получен какой-то другой тип
     * сообщения
     *
     * @throws TranslationException
     */
    public function testTranslationTransInvalidMessage(): void
    {
        $language = null;
        $messages = [
            'hello' => [],
        ];

        $translation = new Translation($language, $messages);

        $this->expectException(TranslationException::class);
        $this->expectExceptionMessage(TranslationException::MESSAGE_SHOULD_BE_STRING);

        $translation->trans('hello');
    }

    /**
     * Тест определения языка на основании данных из $_SERVER['HTTP_ACCEPT_LANGUAGE']
     */
    public function testTranslationTransHttpAcceptLanguage(): void
    {
        $language = 'xxXX';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $language;

        $translation = new Translation();

        self::assertEquals('xx', $translation->getLanguage());
    }

    /**
     * Тест исключительной ситуации, когда передан язык по умолчанию, но справочник по переводу отсутствует
     *
     * @throws TranslationException
     */
    public function testTranslationNoDefaultLanguage(): void
    {
        $language = 'xx';
        $defaultLanguage = 'yy';

        $this->expectException(TranslationException::class);
        $this->expectExceptionMessage(TranslationException::DEFAULT_MESSAGES_NOT_FOUND);
        new Translation($language, null, null, $defaultLanguage);
    }

    /**
     * @param string $language
     * @return array
     */
    private function getMessages(string $language): array
    {
        return require __DIR__ . '/../../../translations/battle/' . $language . '/messages.php';
    }
}
