<?php

declare(strict_types=1);

namespace Tests\Battle\Translation;

use Battle\Translation\Translation;
use Battle\Translation\TranslationException;
use PHPUnit\Framework\TestCase;

class TranslationTest extends TestCase
{
    /**
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

    public function testTranslationTransHttpAcceptLanguage(): void
    {
        $language = 'xx';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $language;

        $translation = new Translation();

        self::assertEquals($language, $translation->getLanguage());
    }

    /**
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
