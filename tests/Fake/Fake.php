<?php

declare(strict_types=1);
use Foo\Bar;

class Fake
{
    public function run()
    {
        new Author;
        new Bar;
        new LogicException;
    }
}
