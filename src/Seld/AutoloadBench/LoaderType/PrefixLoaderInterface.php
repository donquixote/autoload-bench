<?php

namespace Seld\AutoloadBench\LoaderType;


interface PrefixLoaderInterface extends ClassLoaderInterface
{
    function setPrefixes(array $prefixes);
}