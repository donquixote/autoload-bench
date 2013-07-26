<?php

namespace Seld\AutoloadBench\Loader;

use Seld\AutoloadBench\Loader\Krautoload\NamespacePlugin;
use Seld\AutoloadBench\LoaderType\AbstractMultiLoader;

/*
 * Copied from Krautoload sources (GPL licensed)
 *
 * (c) Andreas Hennings <andreas@dqxtech.net>
 */
class KrautoloadSmartUnplugged extends AbstractMultiLoader
{
    /**
     * Nested array, where
     * - the top-level keys are logical base paths obtained from base namespaces,
     *   each with trailing directory separator.
     * - the second-level keys are physical base directories,
     *   each with trailing directory separator.
     * - the second-level values are NamespacePathPlugin_Interface objects.
     *
     * @var array
     */
    protected $namespaceMap = array();

    /**
     * @inheritdoc
     */
    public function add($namespace, $rootDir) {
        $logicalBasePath = $this->namespaceLogicalPath($namespace);
        $baseDir = $rootDir . DIRECTORY_SEPARATOR . $logicalBasePath;
        $this->namespaceMap[$logicalBasePath[0]][$logicalBasePath][$baseDir] = new NamespacePlugin($this->filesystem);
    }

    /**
     * @inheritdoc
     */
    function loadClass($class) {

        // Discard initial namespace separator.
        if ('\\' === $class[0]) {
            $class = substr($class, 1);
        }

        // First check if the literal class name is registered.
        if (isset($this->classMap[$class])) {
            // $this->classMap[$class];
            return TRUE;
        }

        // Distinguish namespace vs underscore-only.
        // This is an internal implementation choice, and has nothing to do with
        // whether or not the PSR-0 spec is correctly implemented.
        if (FALSE !== $lastpos = strrpos($class, '\\')) {

            // Loop through positions of '\\', backwards.
            $logicalPath = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
            $first = $logicalPath[0];
            if (isset($this->namespaceMap[$first])) {
                foreach ($this->namespaceMap[$first] as $logicalBasePath => $plugins) {
                    if (0 === strpos($logicalPath, $logicalBasePath)) {
                        $pos = strlen($logicalBasePath);
                        $len = $lastpos - $pos;
                        /**
                         * @var NamespacePlugin $plugin
                         */
                        foreach ($plugins as $baseDir => $plugin) {
                            // We need to replace the underscores after the last directory separator.
                            if (1
                                && $plugin instanceof NamespacePlugin
                                && $this->filesystem->file_exists($file
                                    = $baseDir
                                    . substr($logicalPath, $pos, $len)
                                    . str_replace('_', DIRECTORY_SEPARATOR, substr($logicalPath, $lastpos))
                                )
                            ) {
                                /** @noinspection PhpExpressionResultUnusedInspection */
                                $file;
                                return TRUE;
                            }
                        }
                    }
                }
            }
        }
        else {

            // The class is not within a namespace.
            // Fall back to the prefix-based finder.
            throw new \Exception("Prefix-only is not happening in this benchmark.");
        }

        return FALSE;
    }

    /**
     * Replace the namespace separator with directory separator.
     *
     * @param string $namespace
     *   Namespace without trailing namespace separator.
     *
     * @return string
     *   Path fragment representing the namespace, with trailing DIRECTORY_SEPARATOR.
     */
    protected function namespaceLogicalPath($namespace) {
        $namespace = trim($namespace, '\\');
        return '' !== $namespace
          ? str_replace('\\', DIRECTORY_SEPARATOR, $namespace . '\\')
          : ''
          ;
    }
}
