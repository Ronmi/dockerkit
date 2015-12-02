<?php

namespace FruitTest\DockerKit;

use PHPUnit_Framework_TestCase;
use Fruit\DockerKit\Dockerfile;
use Fruit\DockerKit\ServiceStarter;

class ServiceStarterTest extends PHPUnit_Framework_TestCase
{
    private function ndf()
    {
        return new Dockerfile('debian', 'Ronmi Ren <ronmi.ren@gmail.com>');
    }

    public function testWithHolder()
    {
        $f = $this->ndf();
        $s = new ServiceStarter('/entry.sh');
        $s->starter('service nginx start');
        $s->starters(['service php-fpm start', 'service mysql-server start']);
        $s->stoper('service nginx stop');
        $s->stopers(['service php-fpm stop', 'service mysql-server stop']);
        $s->reloader('service nginx restart');
        $s->reloaders(['service php-fpm restart', 'service mysql-server restart']);
        $s->holder([
            '(kill -SIGSTOP $BASHPID)&',
            'wait'
        ]);
        $s->installTo($f);

        $actual = $f->generate();
        $expect = file_get_contents(__DIR__ . '/asset/dockerfile.servicestarter');
        $this->assertEquals($expect, $actual);
    }

    public function testWithoutHolder()
    {
        $f = $this->ndf();
        $s = new ServiceStarter('/entry.sh');
        $s->starter('service nginx start');
        $s->starters(['service php-fpm start', 'service mysql-server start']);
        $s->stoper('service nginx stop');
        $s->stopers(['service php-fpm stop', 'service mysql-server stop']);
        $s->reloader('service nginx restart');
        $s->reloaders(['service php-fpm restart', 'service mysql-server restart']);
        $s->installTo($f);

        $actual = $f->generate();
        $expect = file_get_contents(__DIR__ . '/asset/dockerfile.servicestarter');
        $this->assertEquals($expect, $actual);
    }
}
