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

    /**
     * @return string
     */
    public function generate()
    {
        $this->arg('-');
        return parent::generate();
    }

    public function run(Dockerfile $file)
    {
        $cmd = $this->generate();
        $proc = proc_open($cmd, [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes);
        fwrite($pipes[0], $file->generate());
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        return [$output, $err, proc_close($proc)];
    }
}
