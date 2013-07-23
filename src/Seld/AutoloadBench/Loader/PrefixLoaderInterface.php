<?php

namespace Seld\AutoloadBench\Loader;


interface PrefixLoaderInterface extends ClassLoaderInterface
{
    function setPrefixes(array $prefixes);
}