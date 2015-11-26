<?php

namespace Fruit\DockerKit;

interface Module
{
    /// return a Dockerfile instance
    public function export();
    
    /// If this distro does not using bash as default /bin/sh, do the magic here.
    /// class Dockerfile does not use this method, because we cannot detect whether bash is default /bin/sh.
    public function ensureBash();
}