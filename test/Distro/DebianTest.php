<?php

namespace FruitTest\DockerKit\Distro;

use PHPUnit_Framework_TestCase;
use Fruit\DockerKit\Distro\Debian;

class DebianTest extends PHPUnit_Framework_TestCase
{
    public function debianP()
    {
        return array(
            array(new Debian(true), 'Debian.update'),
            array(new Debian(false), 'Debian.noupdate'),
        );
    }
    
    /**
     * @dataProvider debianP
     */
    public function testDebian($debian, $asset)
    {
        $debian
            ->install(array('pkg1'))
            ->setRepo('repo1')
            ->install(array('pkg2'))
            ->addRepo('repo2')
            ->addKeyByString('asd')
            ->addKeyByFingerprint('1234', 'a.b.c')
            ->aptconf('conf', array('val1', 'val2'))
            ->aptpref(array(
                'pref1' => 'val1-1',
                'pref2' => 'val2-1',
            ))
            ->install(array('pkg3'))
            ->ensureBash();
        
        $expect = file_get_contents(__DIR__ . '/asset/' . $asset);
        $actual = $debian->export()->generate();
        $this->assertEquals($expect, $actual);
    }
}