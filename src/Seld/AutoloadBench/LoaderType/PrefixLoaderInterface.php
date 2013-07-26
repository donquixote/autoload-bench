<?php

namespace Seld\AutoloadBench\LoaderType;


interface PrefixLoaderInterface extends ClassLoaderInterface
{
    function add($prefix, $baseDirs);
}