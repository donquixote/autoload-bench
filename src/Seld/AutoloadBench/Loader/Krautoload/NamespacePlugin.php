<?php

namespace Seld\AutoloadBench\Loader\Krautoload;

/*
 * Copied from Krautoload sources (GPL licensed)
 *
 * (c) Andreas Hennings <andreas@dqxtech.net>
 */
use Seld\AutoloadBench\MockFilesystem;

class NamespacePlugin
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
        // We need to replace the underscores after the last directory separator.
        if (FALSE !== $pos = strrpos($relativePath, DIRECTORY_SEPARATOR)) {
            $relativePath = substr($relativePath, 0, $pos) . str_replace('_', DIRECTORY_SEPARATOR, substr($relativePath, $pos));
        }
        else {
            $relativePath = str_replace('_', DIRECTORY_SEPARATOR, $relativePath);
        }
        // We don't know if the file exists.
        if ($this->filesystem->file_exists($file = $baseDir . $relativePath)) {
            // We assume that the file defines the class.
            // include $file;
            return TRUE;
        }
    }
}
