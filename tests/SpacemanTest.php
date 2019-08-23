<?php

declare(strict_types=1);

namespace Koriym\Spaceman;

use PHPUnit\Framework\TestCase;

class SpacemanTest extends TestCase
{
    /**
     * @var Spaceman
     */
    protected $spaceman;

    protected function setUp() : void
    {
        $this->spaceman = new Spaceman;
    }

    public function testIsInstanceOfSpaceman() : void
    {
        $actual = $this->spaceman;
        $this->assertInstanceOf(Spaceman::class, $actual);
    }
}
