<?php

declare(strict_types=1);

namespace Koriym\Spaceman;

class Convert
{
    /**
     * Base namespace
     *
     * @var string
     */
    private $baseNs;

    public function __construct(string $baseNs)
    {
        $this->baseNs = $baseNs;
    }

    public function __invoke(string $sourcePath) : void
    {
        if (! is_dir($sourcePath)) {
            throw new \RuntimeException($sourcePath);
        }
        $phpFiles = new PhpFileGenerator;
        $spaceman = new Spaceman;
        foreach ($phpFiles($sourcePath) as $phpFile) {
            /** @var \SplFileInfo $phpFile */
            $namespace = $this->getNamespace($sourcePath, $phpFile);
            $filePath = $phpFile->getRealPath();
            $code = file_get_contents($filePath);
            if (! is_string($code)) {
                throw new \RuntimeException($filePath);
            }
            $newCode = $spaceman($code, $namespace);
            if ($newCode) {
                file_put_contents($filePath, $newCode);
            }
        }
    }

    private function getNamespace(string $sourcePath, \SplFileInfo $phpFile) : string
    {
        $relativePath = substr(str_replace(rtrim($sourcePath, '/'), '', (string) $phpFile->getRealPath()), 1);
        $dirName = dirname($relativePath);
        $hasDiretocty = $dirName !== '.';

        return $hasDiretocty ? $this->baseNs . '\\' . str_replace('/', '\\', $dirName) : $this->baseNs;
    }
}
