<?php

namespace Fruit\DockerKit;

/**
 * Generate docker command to run something
 */
class DockerRun extends Docker
{
    public function __construct($image)
    {
        parent::__construct('run');
        $this->arg($image);
    }

    /**
     * @return DockerRun
     */
    public function port($host, $cont = -1)
    {
        if ($cont < 1) {
            $cont = $host;
        }
        return $this->option('-p', sprintf('%d:%d', $host, $cont));
    }

    /**
     * @return DockerRun
     */
    public function link($cont, $alias = null)
    {
        if (! $alias) {
            $alias = $cont;
        }
        $this->option('--link', sprintf('%s:%s', $cont, $alias));
        return $this;
    }

    /**
     * @return DockerRun
     */
    public function volume($host, $cont = null)
    {
        if (! $cont) {
            $cont = $host;
        }
        return $this->option('-v', sprintf('%s:%s', $host, $cont));
    }

    /**
     * @return DockerRun
     */
    public function once()
    {
        return $this->option('--rm');
    }

    /**
     * @return DockerRun
     */
    public function tty()
    {
        return $this->option('-t');
    }

    public function shell(array $cmd)
    {
        $this->option('--entrypoint=');
        $this->arg($cmd);
        return $this;
    }

    public function exec(array $cmds)
    {
        $this->option('--entrypoint', array_shift($cmds));
        $this->arg($cmds);
    }

    public function run()
    {
        $cmd = $this->generate();
        exec($cmd, $output, $exitCode);
        return [$output, $exitCode];
    }
}
