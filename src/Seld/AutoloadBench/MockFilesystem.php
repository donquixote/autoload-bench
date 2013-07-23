<?php

namespace Seld\AutoloadBench;

class MockFilesystem
{
    protected $map = array();
    protected $count = 0;

    function setClassMap(array $classMap)
    {
        $this->map = array_fill_keys($classMap, TRUE);
    }

    function reset()
    {
        $this->count = 0;
    }

    function getCount()
    {
        return $this->count;
    }

    function file_exists($file)
    {
        ++$this->count;
        if (!empty($this->map[$file])) {
            return TRUE;
        }
        else {
            print "$file DOES NOT EXIST.\n";
            return FALSE;
        }
    }
}