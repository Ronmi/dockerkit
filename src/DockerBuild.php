<?php

namespace Fruit\DockerKit;

/**
 * Generate docker command to build image.
 */
class DockerBuild extends Docker
{
    public function __construct($name)
    {
        parent::__construct('build');
        $this->option('-t', $name);
    }

    public function generate()
    {
        $this->arg('-');
        return parent::generate();
    }

    public function run(Dockerfile $f)
    {
        $cmd = $this->generate();
        $p = proc_open($cmd, [['pipe', 'r']], $pipes);
        fwrite($pipes[0], $f->generate());
        fclose($pipes[0]);
        proc_close($p);
    }
}
