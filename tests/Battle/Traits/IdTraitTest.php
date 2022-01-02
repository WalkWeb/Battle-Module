<?php

declare(strict_types=1);

namespace Tests\Battle\Traits;

use Battle\Traits\IdTrait;
use Exception;
use Tests\AbstractUnitTest;

class IdTraitTest extends AbstractUnitTest
{
    use IdTrait;

    /**
     * @throws Exception
     */
    public function testIdTraitLimitLength(): void
    {
        $length = 150;
        $limit = 100;

        $string = self::generateId($length);

        self::assertEquals($limit, mb_strlen($string));
    }
}
