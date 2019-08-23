<?php

declare(strict_types=1);

namespace Koriym\Spaceman;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class PrefixingVisitor extends NodeVisitorAbstract
{
    public function enterNode(Node $node) : void
    {
        if ($node instanceof Node\Name) {
            $className = $node->parts[0];
            $node->parts[0] = '\\' . $className;
        }
    }
}
