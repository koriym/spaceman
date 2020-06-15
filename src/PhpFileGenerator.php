<?php

declare(strict_types=1);

namespace Koriym\Spaceman;

class PhpFileGenerator
{
    public function __invoke(string $path) : \Generator
    {
        return $this->generate($path);
    }

    private function generate(string $path) : \Generator
    {
        foreach ($this->filesIterator($path) as $file) {
            /* @var \SplFileInfo $file */
            if (is_int(strpos($file->getPathname(), '/vendor/'))) {
                continue;
            }

            yield $file;
        }
    }

    private function filesIterator(string $path) : \RegexIterator
    {
        return
            new \RegexIterator(
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator(
                        $path,
                        \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS
                    ),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                ),
                '/^.+\.php$/',
                \RecursiveRegexIterator::MATCH
            );
    }
}
