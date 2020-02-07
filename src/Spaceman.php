<?php

declare(strict_types=1);

namespace Koriym\Spaceman;

use Koriym\Spaceman\Exception\InvalidAstException;
use PhpParser\BuilderFactory;
use PhpParser\Lexer\Emulative;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser\Php7;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use PhpParser\PrettyPrinter\Standard;

final class Spaceman
{
    public function __invoke(string $code, string $namespace) : string
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
        if ($this->hasNamespace($oldStmts)) {
            return '';
        }
        $oldTokens = $lexer->getTokens();
        $newStmts = $traverser->traverse($oldStmts);
        // MODIFY $newStmts HERE
        $newStmts = $this->addNamespace($this->resolveName($newStmts), $namespace);

        $printer = new PrettyPrinter\Standard();

        return $printer->printFormatPreserving($newStmts, $oldStmts, $oldTokens);
    }

    /**
     * @return Node[]
     */
    private function getAst(string $code) : array
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse($code);
        if ($ast === null) {
            throw new InvalidAstException($code);
        }

        return $ast;
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

    private function getPrintedCode($newAst) : string
    {
        return (new Standard)->prettyPrintFile($newAst);
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
}
