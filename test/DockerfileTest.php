<?php

namespace FruitTest\DockerKit;

use PHPUnit_Framework_TestCase;
use Fruit\DockerKit\Dockerfile;

class DockerfileTest extends PHPUnit_Framework_TestCase
{
    private function ndf()
    {
        return new Dockerfile('debian', 'Ronmi Ren <ronmi.ren@gmail.com>');
    }

    public function testGenerate()
    {
        $g = $this->ndf();
        $g
            ->add(array('file1', 'file2', 'dest/'))
            ->shell('echo "test" && ' . "echo 'test'")
            ->shellAs('echo "test" && ' . "echo 'test'", 'user')
            ->exec(array('service', 'foo', 'start'))
            ->execAs(array('service', 'foo', 'start'), 'user')
            ->bash('echo "test"')
            ->bashAs('echo "test"', 'user')
            ->sudo('echo "test"', '', ['-E'])
            ->expose(array(8080, 3128))
            ->volume(array('/test'))
            ->user('root')
            ->workdir('/test')
            ->raw('COPY a b')
            ->entrypoint('echo "test" && ' . "echo 'test'")
            ->entrypoint(array('service', 'foo', 'start', 'echo "orz"'));

        $actual = $g->generate();
        $expect = file_get_contents(__DIR__ . '/asset/dockerfile');
        $this->assertEquals($expect, $actual);
    }

    public function testGrouping()
    {
        $g = $this->ndf();
        $g->shell('a')
            ->shell('b')
            ->grouping(true)
            ->shell('c')
            ->shell('d')
            ->grouping(false)
            ->shell('e')
            ->grouping(true)
            ->shell('f')
            ->exec(['g', 'g'])
            ->shell('h')
            ->shell('i')
            ->gReset()
            ->shell('j')
            ->shell('k');

        $actual = $g->generate();
        $expect = file_get_contents(__DIR__ . '/asset/dockerfile.mergerun');
        $this->assertEquals($expect, $actual);
    }

    public function testLastNCommand()
    {
        $g = $this->ndf();
        $g
            ->shell('a')
            ->shell('b');
        $res = $g->lastNCommand(2);
        $res[0] .= 'c';
        $g->lastNCommand(2, $res);

        $actual = $g->generate();
        $expect = file_get_contents(__DIR__ . '/asset/dockerfile.lastn');
        $this->assertEquals($expect, $actual);
    }
}
