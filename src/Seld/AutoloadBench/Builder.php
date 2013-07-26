<?php

namespace Seld\AutoloadBench;

use Seld\AutoloadBench\LoaderType\ClassMapLoaderInterface;
use Seld\AutoloadBench\LoaderType\PrefixLoaderInterface;
use Seld\AutoloadBench\LoaderType\Psr4LoaderInterface;

class Builder
{
    protected $loaderClasses = [];
    protected $filesystem;

    public function __construct(MockFilesystem $filesystem, array $excludedLoaders = array())
    {
        $this->filesystem = $filesystem;
        $excludedLoaders = array_combine($excludedLoaders, $excludedLoaders);
        foreach (glob(__DIR__.'/Loader/*.php') as $file) {
            $name = basename($file, '.php');
            if (!empty($excludedLoaders[$name])) {
                continue;
            }
            $class = 'Seld\AutoloadBench\Loader\\' . $name;
            $refClass = new \ReflectionClass($class);
            if (!$refClass->isAbstract() && !$refClass->isInterface()) {
                $this->loaderClasses[$name] = $class;
            }
        }
    }

    public function buildLoaders(array $classMap, array $prefixes) {
        $loaders = array();
        $this->filesystem->setClassMap($classMap);
        foreach ($this->loaderClasses as $name => $loaderClass) {
            if (is_a($loaderClass, 'Seld\AutoloadBench\LoaderType\ClassMapLoaderInterface', TRUE)) {
                /**
                 * @var ClassMapLoaderInterface $loader
                 */
                $loader = new $loaderClass;
                $loader->setFilesystem($this->filesystem);
                $loader->setClassMap($classMap);
                $loaders['CLASSMAP:     ' . $name] = $loader;
            }
            if (is_a($loaderClass, 'Seld\AutoloadBench\LoaderType\PrefixLoaderInterface', TRUE)) {
                /**
                 * @var PrefixLoaderInterface $loader
                 */
                $loader = new $loaderClass;
                $loader->setFilesystem($this->filesystem);
                $this->loaderAddPrefixes($loader, $prefixes);
                $loaders['PSR-0:        ' . $name] = $loader;
            }
            if (is_a($loaderClass, 'Seld\AutoloadBench\LoaderType\Psr4LoaderInterface', TRUE)) {

                /**
                 * @var Psr4LoaderInterface $loader
                 */
                $loader = new $loaderClass;
                $this->loaderAddAsPsr4($loader, $prefixes);
                // var_dump($loader); exit();
                $loader->setFilesystem($this->filesystem);
                $loaders['PSR-4:        ' . $name] = $loader;

                /**
                 * @var Psr4LoaderInterface $loader
                 */
                $loader = new $loaderClass;
                $loader->setFilesystem($this->filesystem);
                $this->loaderAddPrefixes($loader, $prefixes, TRUE);
                $this->loaderAddAsPsr4($loader, $prefixes);
                $loaders['PSR-4 vs -0F: ' . $name] = $loader;

                /**
                 * @var Psr4LoaderInterface $loader
                 */
                $loader = new $loaderClass;
                $loader->setFilesystem($this->filesystem);
                $this->loaderAddAsPsr4($loader, $prefixes, TRUE);
                $this->loaderAddPrefixes($loader, $prefixes);
                $loaders['PSR-0 vs -4F: ' . $name] = $loader;

                /**
                 * @var Psr4LoaderInterface $loader
                 */
                $loader = new $loaderClass;
                $loader->setFilesystem($this->filesystem);
                $this->loaderAddPrefixes($loader, $prefixes);
                $loader->addPsr4('', 'src-fallback0');
                $loader->addPsr4('', 'src-fallback1');
                $loader->addPsr4('', 'src-fallback2');
                $loaders['PSR-0 vs 4FB: ' . $name] = $loader;
            }
        }
        return $loaders;
    }

    protected function loaderAddPrefixes(PrefixLoaderInterface $loader, $prefixes, $failPrefix = FALSE, $failFile = FALSE)
    {
        foreach ($prefixes as $prefix => $baseDirs) {
            if ($failPrefix) {
                $loader->add($prefix . '.FAIL', $baseDirs);
            }
            elseif ($failFile) {
                $baseDirsFail = array();
                foreach ((array)$baseDirs as $baseDir) {
                    $baseDirsFail[] = $baseDir . '.FAIL';
                }
                $loader->add($prefix, $baseDirsFail);
            }
            else {
                $loader->add($prefix, $baseDirs);
            }
        }
    }

    protected function loaderAddAsPsr4(Psr4LoaderInterface $loader, $prefixes, $failPrefix = FALSE, $failFile = FALSE)
    {
        foreach ($prefixes as $namespace => $baseDirs) {
            $dirSuffix = strtr($namespace, '\\', DIRECTORY_SEPARATOR);
            $baseDirsPsr4 = array();
            foreach ((array)$baseDirs as $baseDir) {
                if ($failFile) {
                    $dirSuffix .= '.FAIL';
                }
                $baseDirsPsr4[] = $baseDir . DIRECTORY_SEPARATOR . $dirSuffix;
            }
            if ($failPrefix) {
                $namespace .= '.FAIL';
            }
            $loader->addPsr4($namespace, $baseDirsPsr4);
        }
    }
}
