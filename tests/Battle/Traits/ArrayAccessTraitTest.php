<?php

declare(strict_types=1);

namespace Tests\Battle\Traits;

use Battle\Traits\ArrayAccessTrait;
use Tests\AbstractUnitTest;

// TODO Временный текст, который будет удален вместе с Trait в будущем

class ArrayAccessTraitTest extends AbstractUnitTest
{
    use ArrayAccessTrait;

    private $elements = [];

    public function testArrayAccessTraitOffsetExists(): void
    {
        self::assertFalse($this->offsetExists(0));

        $this->elements[] = 'a';

        self::assertTrue($this->offsetExists(0));

        $this->elements[] = 'b';

        self::assertTrue($this->offsetExists(1));
    }

    public function testArrayAccessTraitOffsetGet(): void
    {
        $this->elements[] = 'a';

        self::assertEquals('a', $this->offsetGet(0));

        $this->elements[] = 'b';

        self::assertEquals('b', $this->offsetGet(1));
    }

    public function testArrayAccessTraitOffsetSet(): void
    {
        $offset = 'offset';
        $value = 'value';

        $this->offsetSet($offset, $value);

        self::assertTrue($this->offsetExists($offset));
        self::assertEquals($value, $this->offsetGet($offset));
    }

    public function testArrayAccessTraitOffsetUnset(): void
    {
        $offset = 'offset';
        $value = 'value';

        $this->offsetSet($offset, $value);

        self::assertTrue($this->offsetExists($offset));
        self::assertEquals($value, $this->offsetGet($offset));

        $this->offsetUnset($offset);

        self::assertFalse($this->offsetExists($offset));
    }
}
