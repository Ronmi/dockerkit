<?php

namespace Fruit\DockerKit;

class Docker
{
    private $action;
    private $args;
    private $options;

    public function __construct($action)
    {
        $this->action = $action;
        $this->args = array();
    }

    protected function option($opt, $val = null)
    {
        $this->options[] = $opt;
        if ($val !== null) {
            $this->options[] = escapeshellarg($val);
        }
        return $this;
    }

    protected function arg($arg)
    {
        if (is_array($arg)) {
            $this->args = array_merge($this->args, $arg);
            return $this;
        }

        $this->args[] = $arg;
        return $this;
    }

    public function generate()
    {
        return sprintf(
            'docker %s %s %s',
            $this->action,
            implode(' ', $this->options),
            implode(' ', array_map('escapeshellarg', $this->args))
        );
    }
}
