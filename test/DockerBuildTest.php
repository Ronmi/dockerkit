<?php

namespace FruitTest\DockerKit;

use PHPUnit_Framework_TestCase;
use Fruit\DockerKit\DockerBuild;
use Fruit\DockerKit\Dockerfile;

class DockerBuildTest extends PHPUnit_Framework_TestCase
{
    public function testGenerate()
    {
        $g = new DockerBuild('name');

        $actual = $g->generate();
        $expect = "docker build -t 'name' '-'";
        $this->assertEquals($expect, $actual);
    }
}
