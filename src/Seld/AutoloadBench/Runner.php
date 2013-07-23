<?php

namespace Seld\AutoloadBench;

use Seld\AutoloadBench\Loader\ClassLoaderInterface;

class Runner
{
    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var array
     */
    protected $classMap = array();

    /**
     * @var array
     */
    protected $loaders = array();

    /**
     * @var array
     */
    protected $stats = array();

    public function __construct() {
        $this->filesystem = new MockFilesystem();
        $this->builder = new Builder($this->filesystem);
    }

    public function prepare(array $baseLevels, array $childLevels, $total)
    {
        $fixtureClasses = (new Generator)->generate($baseLevels, $childLevels, $total);
        $classMap = [];
        $prefixes = [];
        foreach ($fixtureClasses as $prefix => $suffixes) {
            $prefixes[$prefix] = 'xyzbase';
            foreach ($suffixes as $suffix => $true) {
                $class = $prefix . '\\' . $suffix;
                $classMap[$class] = 'xyzbase' . str_replace('\\', DIRECTORY_SEPARATOR, '\\' . $class) . '.php';
            }
        }
        $this->loaders = $this->builder->buildLoaders($classMap, $prefixes);
        $this->classMap = $classMap;

        echo PHP_EOL . PHP_EOL;
        echo 'Generating: ' . implode('\\', $baseLevels) . ':\\' . implode('\\', $childLevels) . " * $total" . PHP_EOL;
        echo '-------------------------------' . PHP_EOL . PHP_EOL;

        return $this;
    }

    public function run(array $series, $runs = 1)
    {
        $total = $runs * count($this->loaders);
        foreach ($series as $k => $load) {
            $results = $this->runSeries($load, $runs, $total);
            $this->printSeriesResults($results, $runs * $load);
            $this->stats[$k] = $results;
        }
        return $this;
    }

    protected function generateSeries($load)
    {
        if ($load > $m = count($this->classMap)) {
            throw new \Exception("$load > $m");
        }
        if ($load > 0) {
            $toLoad = array_rand($this->classMap, $load);
        }
        else {
            $toLoad = [];
            foreach (array_rand($this->classMap, -$load) as $class) {
                $toLoad[] = '_FAIL_' . $class;
            }
        }
        return $toLoad;
    }

    protected function runSeries($load, $runs, $total)
    {
        $toLoad = $this->generateSeries($load);
        $expected = ($load > 0);
        echo 'Starting '.$total.' runs ('.($load > 0 ? $load : 'fail '.abs($load)).' classes)'.PHP_EOL;
        $run = 0;
        $results = [];
        for ($i = 0; $i < $runs; ++$i) {
            /**
             * @var ClassLoaderInterface $loader
             */
            foreach ($this->loaders as $name => $loader) {
                $start = microtime(true);
                $this->filesystem->reset();
                foreach ($toLoad as $class) {
                    if ($expected !== $loaderResult = $loader->loadClass($class)) {
                        if (FALSE === $loaderResult) {
                            throw new \RuntimeException($name.' failed to load '.$class);
                        }
                        elseif (TRUE === $loaderResult) {
                            throw new \RuntimeException($name.' must not load '.$class);
                        }
                        else {
                            throw new \RuntimeException($name.' must return TRUE or FALSE.');
                        }
                    }
                }
                $results[$name]['runs'][$run] = microtime(true) - $start;
                $results[$name]['class_exists'][$run] = $this->filesystem->getCount();

                $run++;
                if ($run > 0 && (($run-1) % 80) === 0) {
                    echo PHP_EOL;
                }
                echo '.';
            }
        }
        return $results;
    }

    protected function printSeriesResults($results, $runs)
    {
        $fastest = PHP_INT_MAX;
        foreach ($results as $name => $data) {
            $results[$name]['avg'] = array_sum($data['runs']) / $runs;
            $results[$name]['class_exists'] = array_sum($data['class_exists']) / $runs;
            $fastest = min($fastest, $results[$name]['avg']);
        }

        uasort($results, function ($a, $b) {
            if ($a['avg'] === $b['avg']) {
                return 0;
            }

            return $a['avg'] > $b['avg'] ? 1 : -1;
        });

        $matrix = [['LOADER', 'DURATION', 'RATIO', 'CLASS_EXISTS']];
        foreach ($results as $name => $data) {
            $row = array();
            $row[] = '> ' . $name . ':';
            $row[] = sprintf('%.6fμs', $data['avg'] * 1000 * 1000);
            $row[] = sprintf('(%.2fx)', $data['avg'] / $fastest);
            $row[] = $data['class_exists'];
            $matrix[] = $row;
        }

        echo PHP_EOL.PHP_EOL;
        $this->printTable($matrix, [0, 1, 1]);

        echo PHP_EOL;
    }

    protected function printTable(array $matrix, array $align, $glue = ' ')
    {
        $widths = array();
        foreach ($matrix as $iRow => $row) {
            foreach ($row as $iCol => $cell) {
                $l = strlen($cell);
                if (!isset($widths[$iCol]) || $l > $widths[$iCol]) {
                    $widths[$iCol] = $l;
                }
            }
        }
        foreach ($matrix as $iRow => $row) {
            foreach ($row as $iCol => $cell) {
                $row[$iCol] = str_pad($cell, $widths[$iCol], ' ', !empty($align[$iCol]) ? STR_PAD_LEFT : STR_PAD_RIGHT);
            }
            echo implode($glue, $row) . PHP_EOL;
        }
    }
}
