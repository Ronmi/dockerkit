<?php

namespace Fruit\DockerKit\Distro;

use Fruit\DockerKit\Module;

/// All methods here MUST return $this.
interface Distro extends Module
{
    /// If this distro does not using bash as default /bin/sh, do the magic here.
    /// class Dockerfile does not use this method, because we cannot detect whether bash is default /bin/sh.
    public function ensureBash();
}
