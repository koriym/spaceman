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

    public function test__invoke() : void
    {
        $code = file_get_contents(__DIR__ . '/Fake/Foo.php');
        if (! is_string($code)) {
            throw new \RuntimeException;
        }
        $namespace = 'Newspace';
        $sourceCode = ($this->spaceman)($code, $namespace);
        $expected = '<?php

namespace \Newspace;

class Foo
{
    public function run($id)
    {
        $author = new \Author();
    }
}';
        $this->assertSame($expected, $sourceCode);
    }
}
