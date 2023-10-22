<?php

declare(strict_types=1);

namespace Battle\Unit\Ability\Description;

use Battle\BattleException;
use Battle\Container\ContainerException;
use Battle\Container\ContainerInterface;
use Battle\Traits\ValidationTrait;
use Battle\Unit\Ability\AbilityException;

class AbilityDescriptionFactory
{
    use ValidationTrait;

    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param array $data
     * @return AbilityDescriptionInterface
     * @throws AbilityException
     * @throws BattleException
     * @throws ContainerException
     */
    public function create(array $data): AbilityDescriptionInterface
    {
        self::string($data, 'description', AbilityException::INVALID_DESCRIPTION_DATA);
        self::array($data, 'values', AbilityException::INVALID_VALUES_DATA);

        foreach ($data['values'] as $value) {
            if (!is_int($value) && !is_float($value)) {
                throw new AbilityException(AbilityException::INVALID_VALUE_DATA);
            }
        }

        return new AbilityDescription(
            $data['description'],
            $data['values'],
            $this->container->getTranslation()
        );
    }
}
