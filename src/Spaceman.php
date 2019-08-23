<?php

declare(strict_types=1);

namespace Koriym\Spaceman;

use Koriym\Spaceman\Exception\InvalidAstException;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

final class Spaceman
{
    public function __invoke(string $code, string $namespace) : string
    {
        $ast = $this->getAst($code);
        if (! $this->hasNamespace($ast)) {
            $code = $this->addNamespace($ast, $namespace);
        }
        $ast = $this->getAst($code);
        $newAst = $this->addPrefixGlobalNamespace($ast);

        return $this->getPrintedCode($newAst);
    }

    private function getAst(string $code) : array
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse($code);
        if ($ast === null) {
            throw new InvalidAstException($code);
        }

        return $ast;
    }

    private function addNamespace(array $ast, string $namespace) : string
    {
        $factory = new BuilderFactory;
        $node = $factory->namespace($namespace)
            ->addStmts($ast)
            ->getNode();
        $stmts = [$node];
        $prettyPrinter = new Standard;

        return $prettyPrinter->prettyPrintFile($stmts);
    }

    private function hasNamespace(array $ast) : bool
    {
        $traverser = new NodeTraverser();
        $NsCheckerVistor = new NsCheckerVisitor;
        $traverser->addVisitor($NsCheckerVistor);
        $traverser->traverse($ast);

        return $NsCheckerVistor->hasNamespace;
    }

    private function getPrintedCode($newAst) : string
    {
        return (new Standard)->prettyPrintFile($newAst);
    }

    /**
     * @return Node[]
     */
    private function addPrefixGlobalNamespace(array $ast) : array
    {
        $prefixingVisitor = new PrefixingVisitor;
        $traverser = new NodeTraverser();
        $traverser->addVisitor($prefixingVisitor);

        return $traverser->traverse($ast);
    }
}
