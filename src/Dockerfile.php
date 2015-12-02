<?php

namespace Fruit\DockerKit;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class Dockerfile
{
    public static $supportedDistro = array(
        'debian' => 'Fruit\DockerKit\Distro\Debian',
    );

    private $data;
    private $user;
    private $from;
    private $exposed_port;
    private $mountable_volume;
    private $maintainer;
    private $grouping;
    private $tmpGroup;
    private $mergeBegin;
    private $currentDistro;
    private $distroName;

    private function json($str)
    {
        if (defined('JSON_UNESCAPED_SLASHES')) {
            return json_encode($str, \JSON_UNESCAPED_SLASHES);
        }
        return str_replace("\\/", "/", json_encode($str));
    }

    private function jsonStringArray(array $arr)
    {
        array_walk($arr, function (&$v) {
            $v = (string)$v;
        });
        return $this->json($arr);
    }

    public function __construct($from, $maintainer, $defaultUser = 'root')
    {
        $this->from = $from;
        $this->user = $defaultUser;
        $this->maintainer = $maintainer;
        $this->data = array();
        $this->exposed_port = array();
        $this->mountable_volume = array();
        $this->grouping = false;
        $this->tmpGroup = array();
        $this->mergeBegin = false;
        $this->currentDistro = null;
        $this->distroName = '';
    }

    public function distro($distro = null)
    {
        if ($distro === null) {
            return $this->distroName;
        }
        if (array_key_exists($distro, self::$supportedDistro)) {
            $d = self::$supportedDistro[$distro];
            if (!($this->currentDistro instanceof $d)) {
                $this->distroName = $distro;
                $this->currentDistro = new $d($this);
            }
        }
        return $this;
    }

    public function grouping($merge = null)
    {
        if ($merge === null) {
            return $this->grouping;
        }
        $tmp = $merge == true;
        if ($this->grouping != $tmp) {
            $this->grouping = $tmp;
            $this->mergeBegin = false;
        }
        return $this;
    }

    public function gStart($bool)
    {
        array_push($this->tmpGroup, $this->grouping);
        return $this->grouping($bool);
    }

    public function gEnd()
    {
        if (count($this->tmpGroup) < 1) {
            return $this;
        }
        return $this->grouping(array_pop($this->tmpGroup));
    }

    public function appendToFile($content, $path)
    {
        return $this->appendToFileArray(explode("\n", $content), $path);
    }

    public function appendToFileArray(array $content, $path)
    {
        $tmpl = 'echo %s|tee -a %s';
        $merge = $this->grouping();
        $this->grouping(true);
        foreach ($content as $c) {
            $this->shell(sprintf(
                $tmpl,
                escapeshellarg($c),
                escapeshellarg($path)
            ));
        }
        return $this->grouping($merge);
    }

    public function textfile($content, $path)
    {
        return $this->textfileArray(explode("\n", $content), $path);
    }

    /// Create several textfiles at once.
    public function textfiles(array $files)
    {
        foreach ($files as $path => $content) {
            if (!is_array($content)) {
                $content = explode("\n", $content);
            }
            $this->textfileArray($content, $path);
        }
        return $this;
    }

    /// Must not have newline character, or command generated will go wrong.
    public function textfileArray(array $content, $path)
    {
        $tmpl = 'echo %s|%s';
        $merge = $this->grouping();
        $this->grouping(true);
        $first = array_shift($content);
        $this->shell(sprintf($tmpl, escapeshellarg($first), 'tee ' .escapeshellarg($path)));
        foreach ($content as $line) {
            $this->shell(sprintf(
                $tmpl,
                escapeshellarg($line),
                'tee -a ' . escapeshellarg($path)
            ));
        }
        return $this->grouping($merge);
    }

    public function textfileAs($content, $path, $user)
    {
        $olduser = $this->getUser();
        $this->user($user);
        $this->textfile($content, $path);
        return $this->user($olduser);
    }

    public function binaryfile($binary_string, $path)
    {
        $str = base64_encode($binary_string);
        return $this->shell(sprintf("echo '%s'|base64 -d > '%s'", $str, $path));
    }

    public function binaryfileAs($binary_string, $path, $user)
    {
        $olduser = $this->getUser();
        $this->user($user);
        $str = base64_encode($binary_string);
        $this->shell(sprintf("echo '%s'|base64 -d > '%s'", $str, $path));
        return $this->user($olduser);
    }

    public function add(array $files)
    {
        $this->data[] = 'ADD ' . $this->jsonStringArray($files);
        return $this;
    }

    public function shell($cmd)
    {
        if ($this->grouping) {
            $last = count($this->data) - 1;
            if ($last >= 0 and is_array($this->data[$last])) {
                $this->data[$last][] = $cmd;
                return $this;
            }

            $this->data[] = array($cmd);
            return $this;
        }

        $this->data[] = 'RUN ' . $cmd;
        return $this;
    }

    public function shellAs($cmd, $user)
    {
        $olduser = $this->getUser();
        $this->user($user);
        $this->shell($cmd);
        return $this->user($olduser);
    }

    public function exec(array $cmd)
    {
        $this->data[] = 'RUN ' . $this->jsonStringArray($cmd);
        return $this;
    }

    public function execAs(array $cmd, $user)
    {
        $olduser = $this->getUser();
        $this->user($user);
        $this->exec($cmd);
        return $this->user($olduser);
    }

    public function bash($cmd)
    {
        return $this->exec(array('bash', '-l', '-c', $cmd));
    }

    public function bashAs($cmd, $user)
    {
        $olduser = $this->getUser();
        $this->user($user);
        $this->bash($cmd);
        return $this->user($olduser);
    }

    public function entrypoint($cmd)
    {
        $data = 'ENTRYPOINT ' . $this->json($cmd);
        if (! is_array($cmd)) {
            $data = 'ENTRYPOINT ' . $cmd;
        }
        $this->data[] = $data;
        return $this;
    }

    public function expose(array $port)
    {
        $this->exposed_port = array_merge($this->exposed_port, $port);
        return $this;
    }

    public function volume(array $vol)
    {
        $this->mountable_volume = array_merge($this->mountable_volume, $vol);
        return $this;
    }

    public function user($user)
    {
        if ($user != $this->user) {
            $this->data[] = 'USER ' . $user;
            $this->user = $user;
        }
        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function workdir($path)
    {
        $this->data[] = 'WORKDIR ' . $path;
        return $this;
    }

    public function raw($str)
    {
        $this->data[] = $str;
        return $this;
    }

    public function lastNCommand($n = 0, array $rep = null)
    {
        if ($n < 1) {
            $n = 1;
        }

        if ($rep === null) {
            return array_slice($this->data, count($this->data) - $n);
        }

        $this->data = array_merge(
            array_slice($this->data, 0, count($this->data) - $n),
            $rep
        );
    }

    public function generate()
    {
        $ret = 'FROM ' . $this->from . "\n";
        $ret .= 'MAINTAINER ' . $this->maintainer . "\n";
        $data = array_map(function ($v) {
            if (is_array($v)) {
                return 'RUN ' . implode(" \\\n && ", $v);
            }
            return $v;
        }, $this->data);
        $ret .= implode("\n", $data) . "\n";
        if (count($this->exposed_port) > 0) {
            $ports = array_unique($this->exposed_port);
            $ret .= 'EXPOSE ' . implode(' ', $ports) . "\n";
        }
        if (count($this->mountable_volume) > 0) {
            $vols = array_unique($this->mountable_volume);
            $ret .= 'VOLUME ' . $this->jsonStringArray($vols) . "\n";
        }
        return $ret;
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
