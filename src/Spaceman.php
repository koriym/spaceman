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

        [$newStmts, $useStmt] = $this->resolveName($newStmts);
        $newStmts = $this->addNamespace($newStmts, $useStmt, $namespace);
        assert_options(ASSERT_ACTIVE, 0);
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
    private function addNamespace(array $ast, Node\Stmt $useStmt, string $namespace) : array
    {
        $factory = new BuilderFactory;
        $node = $factory->namespace($namespace)
            ->addStmt($useStmt)
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
     * @return array{Node[], Node\Stmt}
     */
    private function resolveName($ast) : array
    {
        $nameResolver = new NameResolver(null, [
            'preserveOriginalNames' => true,
            'replaceNodes' => true,
        ]);
        $nodeTraverser = new NodeTraverser;
        $nodeTraverser->addVisitor($nameResolver);
        $watchVisitor = new GlobalNameClassWatchVisitor;
        $nodeTraverser->addVisitor($watchVisitor);
        $travesedAst = $nodeTraverser->traverse($ast);

        $useStmt = $this->createUseStmt(array_unique($watchVisitor->globalClassNames));

        return [$travesedAst, $useStmt];
    }

    /**
     * @return Node\Stmt\Nop|Node\Stmt\Use_
     */
    private function createUseStmt(array $globalClassNames) : Node\Stmt
    {
        $useUse = [];
        foreach ($globalClassNames as $name) {
            $useUse[] = new Node\Stmt\UseUse(new Node\Name($name));
        }

        return $useUse ? new Node\Stmt\Use_($useUse) : new Node\Stmt\Nop();
    }

    private function addPhpEol(string $code) : string
    {
        if (substr($code, -1) !== "\n") {
            $code .= PHP_EOL;
        }

        return $code;
    }
}
