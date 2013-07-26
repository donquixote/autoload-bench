<?php

namespace Seld\AutoloadBench\LoaderType;


interface Psr4LoaderInterface extends MultiLoaderInterface
{
    function setPrefixesPsr4(array $prefixes);
}