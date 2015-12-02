<?php

namespace Fruit\DockerKit\Dockerfile;

trait Distro
{
    public static $supportedDistro = array(
        'debian' => 'Fruit\DockerKit\Distro\Debian',
    );

    private $currentDistro = null;
    private $distroName = '';

    public function distro($distro = null)
    {
        if ($distro === null) {
            return $this->distroName;
        }
        if (array_key_exists($distro, self::$supportedDistro)) {
            $cls = self::$supportedDistro[$distro];
            if (!($this->currentDistro instanceof $cls)) {
                $this->distroName = $distro;
                $this->currentDistro = new $cls($this);
            }
        }
        return $this;
    }

    /// wrap distro
    public function __call($name, array $args)
    {
        if (method_exists('Fruit\DockerKit\Distro\Distro', $name)) {
            $this->gStart(true);
            call_user_func_array(array($this->currentDistro, $name), $args);
            $this->gEnd();
        }
        return $this;
    }
}
