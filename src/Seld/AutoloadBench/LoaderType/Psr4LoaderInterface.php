<?php

namespace Seld\AutoloadBench\LoaderType;


interface Psr4LoaderInterface extends MultiLoaderInterface
{
    function addPsr4($prefix, $baseDirs);
}