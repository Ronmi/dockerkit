<?php

namespace Fruit\DockerKit;

interface Installer
{
    /// return a Dockerfile instance
    public function installTo(Dockerfile $dest);
}
