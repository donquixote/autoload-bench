<?php

namespace Seld\AutoloadBench\Loader\Krautoload;

/*
 * Copied from Krautoload sources (GPL licensed)
 *
 * (c) Andreas Hennings <andreas@dqxtech.net>
 */
use Seld\AutoloadBench\MockFilesystem;

class NamespacePluginPSRX
{
    /**
     * @var MockFilesystem
     */
    protected $filesystem;

    /**
     * @param MockFilesystem $filesystem
     */
    function __construct(MockFilesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /** @noinspection PhpInconsistentReturnPointsInspection */
    function pluginLoadClass(
        /** @noinspection PhpUnusedParameterInspection */
        $class, $baseDir, $relativePath)
    {
        // We don't know if the file exists.
        if ($this->filesystem->file_exists($file = $baseDir . $relativePath)) {
            // We assume that the file defines the class.
            // include $file;
            return TRUE;
        }
    }
}
