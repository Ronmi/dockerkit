<?php

namespace Fruit\DockerKit;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class Dockerfile
{
    use Dockerfile\Distro;
    use Dockerfile\Exec;
    use Dockerfile\User;
    use Dockerfile\File;
    use Dockerfile\FileSystem;
    use Dockerfile\Misc;

    private $data;
    private $user;
    private $from;
    private $exposedPort;
    private $mountableVolume;
    private $maintainer;
    private $grouping;
    private $readyToMerge;

    private function json($str)
    {
        if (defined('JSON_UNESCAPED_SLASHES')) {
            return json_encode($str, \JSON_UNESCAPED_SLASHES);
        }
        return str_replace("\\/", "/", json_encode($str));
    }

    private function jsonStringArray(array $arr)
    {
        array_walk(
            $arr,
            function (&$val) {
                $val = (string)$val;
            }
        );
        return $this->json($arr);
    }

    public function __construct($from, $maintainer, $defaultUser = 'root')
    {
        $this->from = $from;
        $this->user = $defaultUser;
        $this->maintainer = $maintainer;
        $this->data = array();
        $this->exposedPort = array();
        $this->mountableVolume = array();
        $this->grouping = false;
        $this->readyToMerge = true;
    }

    /**
     * @return Dockerfile
     */
    public function grouping($merge = null)
    {
        if ($merge === null) {
            return $this->grouping;
        }
        $tmp = $merge == true;
        if ($this->grouping != $tmp) {
            $this->grouping = $tmp;
        }
        return $this;
    }

    /**
     * @return Dockerfile
     */
    public function gReset()
    {
        $this->readyToMerge = false;
        return $this;
    }

    /**
     * @return Dockerfile
     */
    public function add(array $files)
    {
        $this->data[] = 'ADD ' . $this->jsonStringArray($files);
        return $this;
    }

    /**
     * @return Dockerfile
     */
    public function shell($cmd)
    {
        $ready = $this->readyToMerge;
        $this->readyToMerge = true;
        if ($this->grouping) {
            $last = count($this->data) - 1;
            if ($ready and $last >= 0 and is_array($this->data[$last])) {
                $this->data[$last][] = $cmd;
                return $this;
            }

            $this->data[] = array($cmd);
            return $this;
        }

        $this->data[] = 'RUN ' . $cmd;
        return $this;
    }

    /**
     * @return Dockerfile
     */
    public function exec(array $cmd)
    {
        $this->data[] = 'RUN ' . $this->jsonStringArray($cmd);
        return $this;
    }

    /**
     * @return Dockerfile
     */
    public function entrypoint($cmd)
    {
        $data = 'ENTRYPOINT ' . $this->json($cmd);
        if (! is_array($cmd)) {
            $data = 'ENTRYPOINT ' . $cmd;
        }
        $this->data[] = $data;
        return $this;
    }

    /**
     * @return Dockerfile
     */
    public function expose(array $port)
    {
        $this->exposedPort = array_merge($this->exposedPort, $port);
        return $this;
    }

    /**
     * @return Dockerfile
     */
    public function volume(array $vol)
    {
        $this->mountableVolume = array_merge($this->mountableVolume, $vol);
        return $this;
    }

    /**
     * @return Dockerfile
     */
    public function user($user)
    {
        if ($user != $this->user) {
            $this->data[] = 'USER ' . $user;
            $this->user = $user;
        }
        return $this;
    }

    /**
     * @return Dockerfile
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Dockerfile
     */
    public function workdir($path)
    {
        $this->data[] = 'WORKDIR ' . $path;
        return $this;
    }

    /**
     * @return Dockerfile
     */
    public function raw($str)
    {
        $this->data[] = $str;
        return $this;
    }

    /**
     * @return array
     */
    public function lastNCommand($num = 0, array $rep = null)
    {
        if ($num < 1) {
            $num = 1;
        }

        if ($rep === null) {
            return array_slice($this->data, count($this->data) - $num);
        }

        $this->data = array_merge(
            array_slice($this->data, 0, count($this->data) - $num),
            $rep
        );
    }

    /**
     * @return string
     */
    public function generate()
    {
        $ret = 'FROM ' . $this->from . "\n";
        $ret .= 'MAINTAINER ' . $this->maintainer . "\n";
        $data = array_map(
            function ($val) {
                if (is_array($val)) {
                    return 'RUN ' . implode(" \\\n && ", $val);
                }
                return $val;
            },
            $this->data
        );
        $ret .= implode("\n", $data) . "\n";
        if (count($this->exposedPort) > 0) {
            $ports = array_unique($this->exposedPort);
            $ret .= 'EXPOSE ' . implode(' ', $ports) . "\n";
        }
        if (count($this->mountableVolume) > 0) {
            $vols = array_unique($this->mountableVolume);
            $ret .= 'VOLUME ' . $this->jsonStringArray($vols) . "\n";
        }
        return $ret;
    }
}
