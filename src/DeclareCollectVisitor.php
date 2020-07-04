<?php

declare(strict_types=1);

namespace Koriym\Spaceman;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class DeclareCollectVisitor extends NodeVisitorAbstract
{
    /**
     * @var Node\Stmt\Declare_[]
     */
    public $declares = [];

    public function enterNode(Node $node): void
    {
        if ($this->isTargetNode($node)) {
            $this->declares[] = $node;
        }
    }

    /**
     * @param Node $node
     * @return int|void
     */
    public function leaveNode(Node $node)
    {
        if ($this->isTargetNode($node)) {
            return NodeTraverser::REMOVE_NODE;
        }
    }

    private function isTargetNode(Node $node): bool
    {
        return $node instanceof Node\Stmt\Declare_;
    }
}
