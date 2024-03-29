<?php

declare(strict_types=1);

namespace Battle\Translation;

class Translation implements TranslationInterface
{
    private const DIR              = 'translations/battle/';
    private const DEFAULT_LANGUAGE = 'en';

    /**
     * Массив переводов в формате:
     *
     * 'оригинал' => 'перевод'
     *
     * @var array
     */
    private array $messages;

    /**
     * @var string
     */
    private string $language;

    /**
     * @param string|null $language
     * @param array|null $messages
     * @param string|null $directory
     * @param string|null $defaultLanguage
     * @throws TranslationException
     */
    public function __construct(
        ?string $language = null,
        ?array $messages = null,
        ?string $directory = null,
        ?string $defaultLanguage = null)
    {
        if ($directory === null) {
            $directory = self::DIR;
        }

        if ($defaultLanguage === null) {
            $defaultLanguage = self::DEFAULT_LANGUAGE;
        }

        $this->language = $language ?? $this->defineLanguage($defaultLanguage);
        $this->messages = $messages ?? $this->getMessages($this->language, $defaultLanguage, $directory);
    }

    /**
     * @param string $message
     * @return string
     */
    public function trans(string $message): string
    {
        if (!array_key_exists($message, $this->messages)) {
            return $message;
        }

        // Просто возвращаем тот же $message, чтобы не бросать исключения во вьюхах
        if (!is_string($this->messages[$message])) {
            return $message;
        }

        return $this->messages[$message];
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @param string $defaultLanguage
     * @return string
     */
    private function defineLanguage(string $defaultLanguage): string
    {
        if (!array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER) || !is_string($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $this->language = $defaultLanguage;
            return $this->language;
        }

        $this->language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) ?: $defaultLanguage;
        return $this->language;
    }

    /**
     * @param string $language
     * @param string $defaultLanguage
     * @param string $directory
     * @return array
     * @throws TranslationException
     */
    private function getMessages(string $language, string $defaultLanguage, string $directory): array
    {
        $path = __DIR__ . '/../../../' . $directory . $language . '/messages.php';

        if (!file_exists($path)) {

            $path = __DIR__ . '/../../../' . $directory . $defaultLanguage . '/messages.php';

            if (!file_exists($path)) {
                throw new TranslationException(TranslationException::DEFAULT_MESSAGES_NOT_FOUND);
            }
        }

        return require $path;
    }
}
