<?php

namespace Seld\AutoloadBench;

use Seld\AutoloadBench\LoaderType\ClassMapLoaderInterface;
use Seld\AutoloadBench\LoaderType\PrefixLoaderInterface;

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
                $loaders['CLASSMAP: ' . $name] = $loader;
            }
            if (is_a($loaderClass, 'Seld\AutoloadBench\LoaderType\PrefixLoaderInterface', TRUE)) {
                /**
                 * @var PrefixLoaderInterface $loader
                 */
                $loader = new $loaderClass;
                $loader->setFilesystem($this->filesystem);
                $loader->setPrefixes($prefixes);
                $loaders['PREFIX:   ' . $name] = $loader;
            }
        }
        return $loaders;
    }
}
