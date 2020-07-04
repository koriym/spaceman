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

        [$newStmts, $declareStmts, $useStmt] = $this->resolveName($newStmts);
        $newStmts = $this->addNamespace($newStmts, $declareStmts, $useStmt, $namespace);
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
    private function addNamespace(array $ast, array $declareStmts, Node\Stmt $useStmt, string $namespace) : array
    {
        $nodes = count($declareStmts) > 0 ? $declareStmts : [];

        $nodes[] = (new BuilderFactory())->namespace($namespace)->getNode();
        $nodes[] = $useStmt;
        $nodes = array_merge($nodes, $ast);

        return $nodes;
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
     * @return array{Node[], Node\Stmt\Declare_[], Node\Stmt}
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
        $declareVisitor = new DeclareCollectVisitor();
        $nodeTraverser->addVisitor($declareVisitor);
        $travesedAst = $nodeTraverser->traverse($ast);

        $useStmt = $this->createUseStmt(array_unique($watchVisitor->globalClassNames));

        return [$travesedAst, $declareVisitor->declares, $useStmt];
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
