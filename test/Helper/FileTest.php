<?php

namespace FruitTest\DockerKit\Helper;

use PHPUnit_Framework_TestCase;
use Fruit\DockerKit\Dockerfile;

class FileTest extends PHPUnit_Framework_TestCase
{
    private function ndf()
    {
        return new Dockerfile('debian', 'Ronmi Ren <ronmi.ren@gmail.com>');
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

    public function testTextfileArray()
    {
        $content = array('qwe', 'asd', 'zxc');
        $path = '/file';

        $g = $this->ndf();
        $g->textfileArray($content, $path);

        $actual = $g->generate();
        $expect = file_get_contents(__DIR__ . '/asset/dockerfile.text');
        $this->assertEquals($expect, $actual);
    }

    public function textfilesP()
    {
        return array(
            array(array("/file" => array('qwe', 'asd', 'zxc'))),
            array(array("/file" => "qwe\nasd\nzxc")),
        );
    }

    /**
     * @dataProvider textfilesP
     */
    public function testTextfiles($content)
    {
        $g = $this->ndf();
        $g->textfiles($content);

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

    public function testAppendToFile()
    {
        $content = implode("\n", array('qwe', 'asd', 'zxc'));
        $path = '/file';

        $g = $this->ndf();
        $g->appendToFile($content, $path);

        $actual = $g->generate();
        $expect = file_get_contents(__DIR__ . '/asset/dockerfile.appendtofile');
        $this->assertEquals($expect, $actual);
    }

    public function testAppendToFileArray()
    {
        $content = array('qwe', 'asd', 'zxc');
        $path = '/file';

        $g = $this->ndf();
        $g->appendToFileArray($content, $path);

        $actual = $g->generate();
        $expect = file_get_contents(__DIR__ . '/asset/dockerfile.appendtofile');
        $this->assertEquals($expect, $actual);
    }
}
