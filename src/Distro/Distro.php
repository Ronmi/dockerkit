<?php

namespace Fruit\DockerKit\Distro;

use Fruit\DockerKit\Module;

/// TODO: There might be some other system functionalities needed to be abstracted.
interface Distro
{
    /**
     * If this distro does not using bash as default /bin/sh, do the magic here.
     * class Dockerfile does not use this method, because we cannot detect whether
     * bash is default /bin/sh.
     */
    public function ensureBash();

    public function tz($tz);
    public function install(array $pkgs);
    public function repo(array $repos);
    public function pkgconf($pkg, $config = null);
    public function pmsconf(array $config);
}
