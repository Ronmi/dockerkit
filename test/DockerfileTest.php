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

    public function testTextfileAs()
    {
        $content = implode("\n", array('qwe', 'asd', 'zxc'));
        $path = '/file';

        $g = $this->ndf();
        $g->textfileAs($content, $path, 'user');

        $actual = $g->generate();
        $expect = file_get_contents(__DIR__ . '/asset/dockerfile.text.user');
        $this->assertEquals($expect, $actual);
    }

    public function testTextfile()
    {
        $content = implode("\n", array('qwe', 'asd', 'zxc'));
        $path = '/file';

        $g = $this->ndf();
        $g->textfile($content, $path);

        $actual = $g->generate();
        $expect = file_get_contents(__DIR__ . '/asset/dockerfile.text');
        $this->assertEquals($expect, $actual);
    }

    public function testBinaryfile()
    {
        $content = 'asd';
        $path = '/test';

        $g = $this->ndf();
        $g->binaryfile($content, $path);

        $actual = $g->generate();
        $expect = file_get_contents(__DIR__ . '/asset/dockerfile.binary');
        $this->assertEquals($expect, $actual);
    }

    public function testBinaryfileAs()
    {
        $content = 'asd';
        $path = '/test';

        $g = $this->ndf();
        $g->binaryfileAs($content, $path, 'user');

        $actual = $g->generate();
        $expect = file_get_contents(__DIR__ . '/asset/dockerfile.binary.user');
        $this->assertEquals($expect, $actual);
    }

    public function testAptget()
    {
        $g = $this->ndf();
        $g->repo(
            'deb http://ftp.debian.org/debian jessie main',
            '/etc/apt/sources.list.d/test.list'
        );
        $g->aptget('pkg1');
        $g->aptgets(array('pkg2', 'pkg3'));

        $actual = $g->generate();
        $expect = file_get_contents(__DIR__ . '/asset/dockerfile.aptget');
        $this->assertEquals($expect, $actual);
    }

    public function testDebconf()
    {
        $g = $this->ndf();
        $g->debconf('dash dash/sh boolean false');
        $g->reconf('dash');

        $actual = $g->generate();
        $expect = file_get_contents(__DIR__ . '/asset/dockerfile.debconf');
        $this->assertEquals($expect, $actual);
    }

    public function testMerge()
    {
        $a = new Dockerfile('a', 'Ronmi Ren <ronmi.ren@gmail.com>');
        $a->shell('echo 1');

        $b = new Dockerfile('b', 'Ronmi Ren <ronmi.ren@gmail.com>');
        $b->shell('echo 2');

        $actual = $a->merge($b)->generate();
        $expect = file_get_contents(__DIR__ . '/asset/dockerfile.merge');
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
          ->shell('i');

        $actual = $g->generate();
        $expect = file_get_contents(__DIR__ . '/asset/dockerfile.mergerun');
        $this->assertEquals($expect, $actual);
    }
}
