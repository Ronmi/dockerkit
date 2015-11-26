<?php

namespace Fruit\DockerKit;

/**
 * Generate docker command to create container.
 */
class DockerCreate extends Docker
{
    public function __construct($image, $name)
    {
        parent::__construct('create');
        $this->arg($image);
        $this->option('--name', $name);
    }

    public function port($host, $cont = -1)
    {
        if ($cont < 1) {
            $cont = $host;
        }
        $this->option('-p', sprintf('%d:%d', $host, $cont));
    }

    public function link($cont, $alias = null)
    {
        if (! $alias) {
            $alias = $cont;
        }
        $this->option('--link', sprintf('%s:%s', $cont, $alias));
    }

    public function volume($host, $cont = null)
    {
        if (! $cont) {
            $cont = $host;
        }
        $this->option('-v', sprintf('%s:%s', $host, $cont));
    }

    public function shell(array $cmd)
    {
        $this->arg($cmd);
    }

    public function exec(array $cmds)
    {
        $cmd = array_shift($cmds);
        $this->option('--entrypoint', $cmd);
        $this->arg($cmds);
    }
}
