<?php

namespace FruitTest\DockerKit\Distro;

use PHPUnit_Framework_TestCase;
use Fruit\DockerKit\Distro\Debian;
use Fruit\DockerKit\Dockerfile;

class DebianTest extends PHPUnit_Framework_TestCase
{
    public function testDebian()
    {
        $dest = new Dockerfile('debian', 'debian');
        $debian = new Debian($dest->grouping(true));
        $debian
            ->install(array('pkg1'))
            ->repo(['repo1' => null, 'repo2' => '1234'])
            ->install(array('pkg2'))
            ->pmsconf([
                'conf' => ['conf' => ['val1', 'val2']],
                'pref' => [
                    'pref1' => 'val1-1',
                    'pref2' => 'val2-1',
                ],
            ])
            ->install(array('pkg3'))
            ->install(array('pkg4'))
            ->pkgconf('pkg1')
            ->pkgconf('pkg2', 'data')
            ->pkgconf('pkg5', ['data' => 'data'])
            ->ensureBash()
            ->tz('Asia/Taipei');

        $expect = file_get_contents(__DIR__ . '/asset/Debian.update');
        $actual = $dest->generate();
        $this->assertEquals($expect, $actual);
    }
}
