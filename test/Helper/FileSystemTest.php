<?php

namespace FruitTest\DockerKit\Helper;

use PHPUnit_Framework_TestCase;
use Fruit\DockerKit\Dockerfile;

class FileSystemTest extends PHPUnit_Framework_TestCase
{
    private function ndf()
    {
        return new Dockerfile('debian', 'Ronmi Ren <ronmi.ren@gmail.com>');
    }

    public function testFileSystem()
    {
        $g = $this->ndf();

        $g->chmod('a+r', '~/test', ['-R']);
        $g->chown('me:us', '~/test', ['-R']);
        $g->symlink('/usr/lib/libtest.*', '/usr/lib/libtest/');
        $g->move('/usr/lib/libtest.*', '/usr/lib/libtest/');
        $g->copy('/usr/lib/libtest.*', '/usr/lib/libtest/');

        $actual = $g->generate();
        $expect = file_get_contents(__DIR__ . '/asset/dockerfile.filesystem');
        $this->assertEquals($expect, $actual);
    }
}
