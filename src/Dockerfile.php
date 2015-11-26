<?php

namespace Fruit\DockerKit;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class Dockerfile
{
    private $data;
    private $user;
    private $from;
    private $exposed_port;
    private $mountable_volume;
    private $maintainer;
    private $grouping;
    private $mergeBegin;

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
        $this->mergeBegin = false;
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

    public function debconf($data)
    {
        return $this->shell(sprintf(
            'echo %s|debconf-set-selections',
            escapeshellarg($data)
        ));
    }

    public function reconf($pkg)
    {
        return $this->shell(sprintf(
            'DEBIAN_FRONTEND=noninteractive dpkg-reconfigure %s',
            $pkg
        ));
    }

    /// Just a short-hand method
    public function enableBash()
    {
        $m = $this->grouping();
        $this->grouping(true);
        $this->debconf('dash dash/sh boolean false');
        $this->reconf('dash');
        return $this->grouping($m);
    }

    public function repo($content, $path)
    {
        $this->textfile($content, $path);
        return $this->shell('apt-get update');
    }

    public function aptget($package)
    {
        return $this->shell('apt-get install -y ' . $package . ' && apt-get clean');
    }

    public function aptgets(array $packages)
    {
        return $this->shell('apt-get install -y ' . implode(' ', $packages) . ' && apt-get clean');
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
            if ($this->mergeBegin) {
                $last = count($this->data) - 1;
                $data = $this->data[$last];
                if (substr($data, 0, 4) == 'RUN ' and !is_array(json_decode(substr($data, 4)))) {
                    $this->data[$last] .= " \\\n && " . $cmd;
                    return $this;
                }
            } else {
                $this->mergeBegin = true;
            }
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

    public function merge(Dockerfile $file)
    {
        $this->data = array_merge($this->data, $file->data);
        $this->expose($file->exposed_port);
        $this->volume($file->mountable_volume);
        return $this;
    }

    public function generate()
    {
        $ret = 'FROM ' . $this->from . "\n";
        $ret .= 'MAINTAINER ' . $this->maintainer . "\n";
        $ret .= implode("\n", $this->data) . "\n";
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
}
