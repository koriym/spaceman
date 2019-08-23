<?php

declare(strict_types=1);

namespace Koriym\Spaceman;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class NsCheckerVisitor extends NodeVisitorAbstract
{
    /**
     * @var bool
     */
    public $hasNamespace = false;

    public function enterNode(Node $node) : void
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            $this->hasNamespace = true;
        }
    }
}
