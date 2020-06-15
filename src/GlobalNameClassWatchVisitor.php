<?php

declare(strict_types=1);

namespace Koriym\Spaceman;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class GlobalNameClassWatchVisitor extends NodeVisitorAbstract
{
    /**
     * @var array<string>
     */
    public $globalClassNames = [];

    public function enterNode(Node $node) : void
    {
        if ($node instanceof Node\Name\FullyQualified && count($node->parts) === 1) {
            $target = $node->parts[0];
            $isGlobalClassName = ! function_exists($target) && ! defined($target);
            if ($isGlobalClassName && ! in_array($target, $this->globalClassNames, true)) {
                $this->globalClassNames[] = $target;
            }
        }
    }
}
