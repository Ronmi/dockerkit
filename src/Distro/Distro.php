<?php

namespace Fruit\DockerKit\Distro;

use Fruit\DockerKit\Module;

/// TODO: There might be some other system functionalities need to be abstracted.
interface Distro
{
    /**
     * If this distro does not using bash as default /bin/sh, do the magic here.
     * class Dockerfile does not use this method, because we cannot detect whether
     * bash is default /bin/sh.
     */
    public function ensureBash();

    /**
     * Setting up system timezone.
     */
    public function tz($tz);

    /**
     * Install packages.
     * The structure of parameter array is up to implementation.
     */
    public function install(array $pkgs);

    /**
     * Setting up package repositories.
     * The structure of parameter array is up to implementation.
     */
    public function repo(array $repos);

    /**
     * Setting up a package with package manage system provided tool.
     * The structure of $config is up to implementation.
     */
    public function pkgconf($pkg, $config = null);

    /**
     * Setting up package manage system.
     * The structure of parameter array is up to implementation.
     */
    public function pmsconf(array $config);
}
