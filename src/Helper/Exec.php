<?php

namespace Fruit\DockerKit\Helper;

trait Exec
{
    /**
     * @return Dockerfile
     */
    public function shellAs($cmd, $user)
    {
        return $this->uStart($user)->shell($cmd)->uEnd();
    }

    /**
     * @return Dockerfile
     */
    public function execAs(array $cmd, $user)
    {
        return $this->uStart($user)->exec($cmd)->uEnd();
    }

    /**
     * @return Dockerfile
     */
    public function bash($cmd)
    {
        return $this->exec(array('bash', '-l', '-c', $cmd));
    }

    /**
     * @return Dockerfile
     */
    public function bashAs($cmd, $user)
    {
        return $this->uStart($user)->bash($cmd)->uEnd();
    }
}
