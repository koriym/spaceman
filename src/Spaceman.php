<?php

declare(strict_types=1);

namespace Koriym\Spaceman;

use PhpParser\BuilderFactory;
use PhpParser\Lexer\Emulative;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser\Php7;
use PhpParser\PrettyPrinter\Standard;

final class Spaceman
{
    /**
     * return namespaced code
     */
    public function __invoke(string $code, string $namespace) : string
    {
        [$oldStmts, $oldTokens, $newStmts] = $this->getAstToken($code);
        if ($this->hasNamespace($oldStmts)) {
            return '';
        }
        $newStmts = $this->addNamespace($this->resolveName($newStmts), $namespace);
        assert_options(ASSERT_ACTIVE, 1);
        $code = (new Standard)->printFormatPreserving($newStmts, $oldStmts, $oldTokens);
        assert_options(ASSERT_ACTIVE, 1);

        return $this->addPhpEol($code);
    }

    private function getAstToken(string $code) : array
    {
        $lexer = new Emulative([
            'usedAttributes' => [
                'comments',
                'startLine', 'endLine',
                'startTokenPos', 'endTokenPos',
            ],
        ]);
        $parser = new Php7($lexer);
        $traverser = new NodeTraverser;
        $traverser->addVisitor(new NodeVisitor\CloningVisitor());
        $oldStmts = $parser->parse($code);
        $oldTokens = $lexer->getTokens();
        $newStmts = $traverser->traverse($oldStmts);

        return [$oldStmts, $oldTokens, $newStmts];
    }

    /**
     * @return Node[]
     */
    private function addNamespace(array $ast, string $namespace) : array
    {
        $factory = new BuilderFactory;
        $node = $factory->namespace($namespace)
            ->addStmts($ast)
            ->getNode();

        return [$node];
    }

    private function hasNamespace(array $ast) : bool
    {
        $traverser = new NodeTraverser();
        $NsCheckerVistor = new NsCheckerVisitor;
        $traverser->addVisitor($NsCheckerVistor);
        $traverser->traverse($ast);

        return $NsCheckerVistor->hasNamespace;
    }

    /**
     * @return Node[]
     */
    private function resolveName($ast) : array
    {
        $nameResolver = new NameResolver();
        $nodeTraverser = new NodeTraverser;
        $nodeTraverser->addVisitor($nameResolver);

        return $nodeTraverser->traverse($ast);
    }

    private function addPhpEol(string $code) : string
    {
        if (substr($code, -1) !== "\n") {
            $code .= PHP_EOL;
        }

        return $code;
    }
}
