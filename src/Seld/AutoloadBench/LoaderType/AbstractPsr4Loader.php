<?php

namespace Seld\AutoloadBench\LoaderType;

use Seld\AutoloadBench\LoaderType\AbstractClassMapLoader;
use Seld\AutoloadBench\LoaderType\PrefixLoaderInterface;

abstract class AbstractPsr4Loader extends AbstractMultiLoader implements Psr4LoaderInterface
{}
