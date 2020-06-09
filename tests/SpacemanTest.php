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
        $code = file_get_contents(__DIR__ . '/Fake/Fake.php');
        if (! is_string($code)) {
            throw new \RuntimeException;
        }
        $namespace = 'Newname\Space';
        $sourceCode = ($this->spaceman)($code, $namespace);
        $expected = <<<'EOT'
<?php

namespace Newname\Space;

use Foo\Bar;
class Fake
{
    public function run()
    {
        new Author;
        new \Foo\Bar;
    }
}

EOT;
        $this->assertSame($expected, $sourceCode);
    }
}
