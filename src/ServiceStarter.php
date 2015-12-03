<?php

namespace Fruit\DockerKit;

/**
 * Make your entry point on-the-fly! See test case for the usage and result.
 */
class ServiceStarter implements Installer
{
    private $path;
    private $starter;
    private $stoper;
    private $reloader;
    private $holder;

    public function __construct($path)
    {
        $this->path = $path;
        $this->starter = array();
        $this->stoper = array();
        $this->reloader = array();
        $this->holder = null;
    }

    /**
     * @return ServiceStarter
     */
    public function starter($starter)
    {
        $this->starter[] = $starter;
        return $this;
    }

    /**
     * @return ServiceStarter
     */
    public function starters(array $starters)
    {
        $this->starter = array_merge($this->starter, $starters);
        return $this;
    }

    /**
     * @return ServiceStarter
     */
    public function stoper($stoper)
    {
        $this->stoper[] = $stoper;
        return $this;
    }

    /**
     * @return ServiceStarter
     */
    public function stopers(array $stopers)
    {
        $this->stoper = array_merge($this->stoper, $stopers);
        return $this;
    }

    /**
     * @return ServiceStarter
     */
    public function reloader($reloader)
    {
        $this->reloader[] = $reloader;
        return $this;
    }

    /**
     * @return ServiceStarter
     */
    public function reloaders(array $reloaders)
    {
        $this->reloader = array_merge($this->reloader, $reloaders);
        return $this;
    }

    /**
     * @return ServiceStarter
     */
    public function holder(array $holder)
    {
        $this->holder = $holder;
        return $this;
    }

    private function gen(array $content, array $arr, $func, $signal = null)
    {
        if (count($arr) < 1) {
            return $content;
        }

        $content[] = "function $func() {";
        $content = array_merge($content, array_map(function ($val) {
            return '  ' . $val;
        }, $arr));
        $content[] = "}";
        if ($signal != null) {
            $content[] = "trap $func $signal";
        }
        $content[] = '';
        return $content;
    }

    public function installTo(Dockerfile $file)
    {
        if ($this->holder == null) {
            $this->holder = array(
                '(kill -SIGSTOP $BASHPID)&',
                'wait'
            );
        }

        $content = array('#!/bin/bash');
        $content = $this->gen($content, $this->starter, 'start');
        $content = $this->gen($content, $this->stoper, 'stop', 'INT TERM');
        $content = $this->gen($content, $this->reloader, 'reload', 'HUP');
        $content = array_merge($content, $this->holder);
        $file
            ->gStart(true)
            ->textfileArray($content, $this->path)
            ->chmod('a+x', $this->path)
            ->gEnd();
    }
}
