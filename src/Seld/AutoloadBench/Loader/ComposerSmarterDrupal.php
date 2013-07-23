<?php

namespace Seld\AutoloadBench\Loader;

/*
 * Copied from Composer sources (MIT licensed)
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * This class is loosely based on the Symfony UniversalClassLoader.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class ComposerSmarterDrupal extends AbstractMultiLoader
{
    private $useIncludePath = false;
    private $prefixLengths = array();
    private $prefixDirs = array();
    private $fallbackDirs = array();

    const PSR0 = 1;
    const PSR4 = 2;
    // The predictor index has a hardcoded value optimized for Drupal.
    // It always picks the third character of the extension name,
    // or "r" for Core, or "m" for Component, or '' if the extension name is too
    // short.
    const PREDICTOR_INDEX_0 = 7;
    const PREDICTOR_INDEX_1 = 9;

    /**
     * Registers a set of classes, merging with any others previously set.
     *
     * @param string       $prefix  The classes prefix
     * @param array|string $paths   The location(s) of the classes
     * @param bool         $prepend Prepend the location(s)
     */
    public function add($prefix, $paths, $prepend = false) {
        $paths = is_array($paths) ? array_fill_keys($paths, self::PSR0) : array($paths => self::PSR0);
        $this->addPrefixPaths($prefix, $paths, $prepend);
    }

    /**
     * @param $extensionName
     * @param $relativeExtensionDir
     * @param bool $prepend
     */
    public function addDrupalExtension($extensionName, $relativeExtensionDir, $prepend = false) {
        $this->addPrefixPaths('Drupal\\' . $extensionName . '\\', array(
                'DRUPAL_ROOT' . DIRECTORY_SEPARATOR . $relativeExtensionDir . '/lib/' => self::PSR0,
                'DRUPAL_ROOT' . DIRECTORY_SEPARATOR . $relativeExtensionDir . '/src/' => self::PSR4,
            ), $prepend);
    }

    /**
     * @param $extensionName
     * @param $relativeExtensionDir
     * @param bool $prepend
     */
    public function addDrupalExtensionTests($extensionName, $relativeExtensionDir, $prepend = false) {
        $this->addPrefixPaths('Drupal\\' . $extensionName . '\Tests\\', array(
                'DRUPAL_ROOT' . DIRECTORY_SEPARATOR . $relativeExtensionDir . '/tests/' => self::PSR0,
                'DRUPAL_ROOT' . DIRECTORY_SEPARATOR . $relativeExtensionDir . '/tests/src/' => self::PSR4,
            ), $prepend);
    }

    protected function addPrefixPaths($prefix, array $paths, $prepend) {
        if (!$prefix) {
            if ($prepend) {
                $this->fallbackDirs = array_merge(
                    (array) $paths,
                    $this->fallbackDirs
                );
            }
            else {
                $this->fallbackDirs = array_merge(
                    $this->fallbackDirs,
                    (array) $paths
                );
            }

            return;
        }
        if (!isset($this->prefixDirs[$prefix])) {
            $predictor = $prefix[0];
            if (isset($prefix[self::PREDICTOR_INDEX_1])) {
                $predictor .= $prefix[self::PREDICTOR_INDEX_0] . $prefix[self::PREDICTOR_INDEX_1];
            }
            $this->prefixLengths[$predictor][$prefix] = strlen($prefix);
            $this->prefixDirs[$prefix] = $paths;
        }
        elseif ($prepend) {
            $this->prefixDirs[$prefix] += $paths;
        }
        else {
            $this->prefixDirs[$prefix] = $paths + $this->prefixDirs[$prefix];
        }
    }

    /**
     * Loads the given class or interface.
     *
     * @param string $class
     *   The name of the class
     * @param bool $returnFile
     *   Whether to return the file or just TRUE.
     * @return bool|string|null
     *   FALSE, if not found.
     *   TRUE, if loaded and $returnFile was FALSE.
     *   The file path, if $returnFile was TRUE and the file was found.
     */
    public function loadClass($class, $returnFile = FALSE) {
        // work around for PHP 5.3.0 - 5.3.2 https://bugs.php.net/50731
        if ('\\' == $class[0]) {
            $class = substr($class, 1);
        }

        if (isset($this->classMap[$class])) {
            $this->classMap[$class];
            return true;
        }

        if (false !== $pos = strrpos($class, '\\')) {
            // namespaced class name
            $namespacePath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos + 1));
            // $classPath = strtr(substr($class, 0, $pos), '\\', DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $className = substr($class, $pos + 1);
        }
        else {
            // PEAR-like class name
            $namespacePath = '';
            // $classPath = null;
            $className = $class;
        }

        $classNamePathPSR0 = strtr($className, '_', DIRECTORY_SEPARATOR) . '.php';

        $first = $class[0];
        if (isset($class[self::PREDICTOR_INDEX_1])) {
            $predictor = $first . $class[self::PREDICTOR_INDEX_0] . $class[self::PREDICTOR_INDEX_1];
            if (isset($this->prefixLengths[$predictor])) {
                foreach ($this->prefixLengths[$predictor] as $prefix => $length) {
                    if (0 === strpos($class, $prefix)) {
                        foreach ($this->prefixDirs[$prefix] as $dir => $type) {
                            if (self::PSR0 === $type) {
                                if ($this->filesystem->file_exists($file = $dir . DIRECTORY_SEPARATOR . $namespacePath . $classNamePathPSR0)) {
                                    if ($returnFile) {
                                        return $file;
                                    }
                                    /** @noinspection PhpExpressionResultUnusedInspection */
                                    $file;
                                    return TRUE;
                                }
                            }
                            else {
                                // PSR-4.
                                if ($this->filesystem->file_exists($file = $dir . DIRECTORY_SEPARATOR . substr($namespacePath, $length) . $className . '.php')) {
                                    if ($returnFile) {
                                        return $file;
                                    }
                                    /** @noinspection PhpExpressionResultUnusedInspection */
                                    $file;
                                    return TRUE;
                                }
                            }
                        }
                    }
                }
            }
        }
        if (isset($this->prefixLengths[$first])) {
            foreach ($this->prefixLengths[$first] as $prefix => $length) {
                if (0 === strpos($class, $prefix)) {
                    foreach ($this->prefixDirs[$prefix] as $dir => $type) {
                        if (self::PSR0 === $type) {
                            if ($this->filesystem->file_exists($file = $dir . DIRECTORY_SEPARATOR . $namespacePath . $classNamePathPSR0)) {
                                if ($returnFile) {
                                    return $file;
                                }
                                /** @noinspection PhpExpressionResultUnusedInspection */
                                /** @noinspection PhpExpressionResultUnusedInspection */
                                $file;
                                return TRUE;
                            }
                        }
                        else {
                            // PSR-4.
                            if ($this->filesystem->file_exists($file = $dir . DIRECTORY_SEPARATOR . substr($namespacePath, $length) . $className . '.php')) {
                                if ($returnFile) {
                                    return $file;
                                }
                                /** @noinspection PhpExpressionResultUnusedInspection */
                                $file;
                                return TRUE;
                            }
                        }
                    }
                }
            }
        }

        $classPathPSR0 = $namespacePath . $classNamePathPSR0;

        foreach ($this->fallbackDirs as $dir) {
            if ($this->filesystem->file_exists($dir . DIRECTORY_SEPARATOR . $classPathPSR0)) {
                if ($returnFile) {
                    return $dir . DIRECTORY_SEPARATOR . $classPathPSR0;
                }
                /** @noinspection PhpExpressionResultUnusedInspection */
                $dir . DIRECTORY_SEPARATOR . $classPathPSR0;
                return true;
            }
        }

        if ($this->useIncludePath && $file = stream_resolve_include_path($classPathPSR0)) {
            if ($returnFile) {
                return $file;
            }
            /** @noinspection PhpExpressionResultUnusedInspection */
            $file;
            return true;
        }

        return $this->classMap[$class] = false;
    }

    public function findFile($class) {
        return $this->loadClass($class, TRUE);
    }
}
