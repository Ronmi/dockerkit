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

    /**
     * @return Docker
     */
    public function port($host, $cont = -1)
    {
        if ($cont < 1) {
            $cont = $host;
        }
        $this->option('-p', sprintf('%d:%d', $host, $cont));
    }

    /**
     * @return Docker
     */
    public function link($cont, $alias = null)
    {
        if (! $alias) {
            $alias = $cont;
        }
        return $this->option('--link', sprintf('%s:%s', $cont, $alias));
    }

    /**
     * @return Docker
     */
    public function volume($host, $cont = null)
    {
        if (! $cont) {
            $cont = $host;
        }
        return $this->option('-v', sprintf('%s:%s', $host, $cont));
    }

    /**
     * @return Docker
     */
    public function shell(array $cmd)
    {
        return $this->arg($cmd);
    }

    public function exec(array $cmds)
    {
        $cmd = array_shift($cmds);
        $this->option('--entrypoint', $cmd);
        $this->arg($cmds);
    }
}
