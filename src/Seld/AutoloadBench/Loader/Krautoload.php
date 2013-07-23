<?php

namespace Seld\AutoloadBench\Loader;

use Seld\AutoloadBench\Loader\Krautoload\NamespacePlugin;

/*
 * Copied from Krautoload sources (GPL licensed)
 *
 * (c) Andreas Hennings <andreas@dqxtech.net>
 */
class Krautoload extends AbstractMultiLoader
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
        $this->namespaceMap[$logicalBasePath][$baseDir] = new NamespacePlugin($this->filesystem);
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
        if (FALSE !== $pos = strrpos($class, '\\')) {

            // Loop through positions of '\\', backwards.
            $logicalBasePath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos + 1));
            $relativePath = substr($class, $pos + 1) . '.php';
            if ($this->mapLoadClass($this->namespaceMap, $class, $logicalBasePath, $relativePath)) {
                return TRUE;
            }
        }
        else {

            // The class is not within a namespace.
            // Fall back to the prefix-based finder.
            throw new \Exception("Pearlike is not happening in this benchmark.");
        }

        return FALSE;
    }

    /**
     * Find the file for a class that in PSR-0 or PEAR would be in
     * $psr_0_root . '/' . $logicalBasePath . $relativePath
     *
     * @param array $map
     *   Either the namespace map or the prefix
     * @param string $class
     * @param string $logicalBasePath
     *   First part of the canonical path, with trailing DIRECTORY_SEPARATOR.
     * @param string $relativePath
     *   Second part of the canonical path, ending with '.php'.
     *
     * @return bool|NULL
     *   TRUE, if we found the file for the class.
     *   That is, if the $api->suggestFile($file) method returned TRUE one time.
     *   NULL, if we have no more suggestions.
     */
    /** @noinspection PhpInconsistentReturnPointsInspection */
    protected function mapLoadClass(array $map, $class, $logicalBasePath, $relativePath) {

        $path = $logicalBasePath . $relativePath;
        while (TRUE) {
            // Check any plugin registered for this fragment.
            if (!empty($map[$logicalBasePath])) {
                /**
                 * @var NamespacePlugin $plugin
                 */
                foreach ($map[$logicalBasePath] as $baseDir => $plugin) {
                    if ($plugin->pluginLoadClass($class, $baseDir, $relativePath)) {
                        return TRUE;
                    }
                }
            }

            // Continue with parent fragment.
            if ('' === $logicalBasePath) {
                break;
            }
            elseif (DIRECTORY_SEPARATOR === $logicalBasePath) {
                // This happens if a class begins with an underscore.
                $logicalBasePath = '';
                $relativePath = $path;
            }
            elseif (FALSE !== $pos = strrpos($logicalBasePath, DIRECTORY_SEPARATOR, -2)) {
                $logicalBasePath = substr($logicalBasePath, 0, $pos + 1);
                $relativePath = substr($path, $pos + 1);
            }
            else {
                $logicalBasePath = '';
                $relativePath = $path;
            }
        }
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
