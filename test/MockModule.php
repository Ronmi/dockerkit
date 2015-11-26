<?php

namespace FruitTest\DockerKit;

use Fruit\DockerKit\Module;
use Fruit\DockerKit\Dockerfile;

class MockModule implements Module
{
    public function export()
    {
        return (new Dockerfile('b', 'Ronmi Ren <ronmi.ren@gmail.com>'))->shell('echo 2');
    }

    public function ensureBash()
    {
        return $this;
    }
}