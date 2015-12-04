<?php

namespace Fruit\DockerKit\Helper;

use Fruit\DockerKit\Installer;

trait Misc
{
    private $tmpGroup = array();
    private $tmpUser = array();

    /**
     * @return Dockerfile
     */
    public function gStart($bool)
    {
        array_push($this->tmpGroup, $this->grouping);
        return $this->grouping($bool);
    }

    /**
     * @return Dockerfile
     */
    public function gEnd()
    {
        if (count($this->tmpGroup) < 1) {
            return $this;
        }
        return $this->grouping(array_pop($this->tmpGroup));
    }

    /**
     * @return Dockerfile
     */
    public function uStart($user)
    {
        $this->tmpUser[] = $this->user;
        return $this->user($user);
    }

    /**
     * @return Dockerfile
     */
    public function uEnd()
    {
        if (count($this->tmpUser) < 1) {
            return $this;
        }
        return $this->user(array_pop($this->tmpUser));
    }

    /**
     * @return Dockerfile
     */
    public function inst(Installer $installer)
    {
        $installer->installTo($this);
        return $this;
    }
}
