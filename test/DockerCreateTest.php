<?php

namespace FruitTest\DockerKit;

use PHPUnit_Framework_TestCase;
use Fruit\DockerKit\DockerCreate;

class DockerCreateTest extends PHPUnit_Framework_TestCase
{
    public function testGenerate()
    {
        $g = new DockerCreate('img', 'name');
        $g->port(3128);
        $g->port(8080, 8000);
        $g->link('c1');
        $g->link('c2', 'alias');
        $g->volume('/test');
        $g->volume('/src', '/srv');
        $g->shell(array('prog', 'arg'));

        $actual = $g->generate();
        $expect = "docker create --name 'name' -p '3128:3128' -p '8080:8000' --link 'c1:c1' --link 'c2:alias' -v '/test:/test' -v '/src:/srv' 'img' 'prog' 'arg'";
        $this->assertEquals($expect, $actual);
    }

    public function testExec()
    {
        $g = new DockerCreate('img', 'name');
        $g->exec(array('prog', 'arg'));

        $actual = $g->generate();
        $expect = "docker create --name 'name' --entrypoint 'prog' 'img' 'arg'";
        $this->assertEquals($expect, $actual);
    }
}
