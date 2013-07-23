# PHP Autoloader Benchmark (Fork)

This is a fork of Seldaek's (Jordi Boggiano) "autoload-bench",
https://github.com/seldaek/autoload-bench
After doing really a lot of changes, I decided it is too much for a pull
request, and I rather publish my own version.

Goals:  
- Compare a number of different autoload strategies
- Fine-tune those algorithms
- Find the ideal loader for different use cases
- Find the ideal loader strategy for Drupal core.
- Compare loader strategies that support both PSR-0 and the upcoming **PSR-4**.

Loader strategies:  
There are some based on PSR-0 prefix maps, some based on class maps, and some
which can do both.
Especially for the prefix-based PSR-0 loaders, there is a number of different
lookup algorithms to compare.

Use cases:
* Some projects have few registered namespaces, others have a lot.
* Some projects have many namespaces registered within one top-level vendor
  namespace. Others have their namespaces distributed among different vendor
  namespaces.
* Some projects have deeply nested namespace hierarchies, others have rather flat
  ones.

The goal is to find the ideal loader strategy for each case, and also to
identify loaders that are good overall.

The original motivation to fork this was to be able to test the Krautoload
loader.

## Filesystem mocked out.

The original autoload-bench would generate a huge amount of class files in the
filesystem to play with.

The forked autoload-bench does not do this at all, and mocks out file_exists().
It turns out that all loaders that are tested run file_exists() exactly once
(in the given test scenarios), so skipping it is fair for all.


## Usage

    composer install
    php -d apc.enable_cli=1 bin/bench


## Motivation

(by Seldaek)

While [other benchmarks](http://mwop.net/blog/245-Autoloading-Benchmarks.html)
exist already, I needed one that could be run very easily, and that focused on
what IMO is the only differenciator amongst autoloading scripts: the time it takes
to locate a file for a given class name. I have no interest in APC or actually
loading a class, that's PHP's job.

If you figure out a faster way than ClassMap, or manage to optimize it in
any way, please contribute it.

## License (MIT)

> Copyright (c) 2012 Jordi Boggiano, (c) 2013 Andreas Hennings
>
> Permission is hereby granted, free of charge, to any person obtaining a copy
> of this software and associated documentation files (the "Software"), to deal
> in the Software without restriction, including without limitation the rights
> to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
> copies of the Software, and to permit persons to whom the Software is furnished
> to do so, subject to the following conditions:
>
> The above copyright notice and this permission notice shall be included in all
> copies or substantial portions of the Software.
>
> THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
> IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
> FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
> AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
> LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
> OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
> THE SOFTWARE.
