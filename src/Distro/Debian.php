<?php

namespace Fruit\DockerKit\Distro;

use Fruit\DockerKit\Dockerfile;

class Debian implements Distro
{
    const SOURCES = '/etc/apt/sources.list';
    const APTCONF = '/etc/apt/apt.conf.d/99dockerkit';
    const APTPREF = '/etc/apt/preferences.d/99dockerkit';
    private $dest;
    private $updated;

    public function __construct($needUpdate = true)
    {
        $this->dest = new Dockerfile('', '');
        $this->dest->grouping(true);
        $this->updated = $needUpdate != true;
    }

    public function aptget(array $packages)
    {
        if (!$this->updated) {
            $this->dest->shell('apt-get update');
            $this->updated = true;
        }
        $this->dest
            ->shell('apt-get install -y ' . implode(' ', $packages))
            ->shell('apt-get clean');
        return $this;
    }

    public function addKeyByURI($uri)
    {
        $keyStr = file_get_contents($uri);
        $this->updated = false;
        return $this->addKeyByString($keyStr);
    }

    public function addKeyByString($key)
    {
        $this->dest
            ->textfile($key, '/tmp/apt.key')
            ->shell('cat /tmp/apt.key|apt-keys add -')
            ->shell('rm /tmp/apt.key');
        $this->updated = false;
        return $this;
    }

    public function addKeyByFingerprint($fingerprint, $server = 'pgp.mit.edu')
    {
        $this->dest->shell("apt-key adv --recv-key $fingerprint --keyserver $server");
        $this->updated = false;
        return $this;
    }

    public function setRepo($repo)
    {
        $this->dest->textfile($repo, self::SOURCES);
        $this->updated = false;
        return $this;
    }

    public function addRepo($repo)
    {
        $this->dest->appendToFile($repo, self::SOURCES);
        $this->updated = false;
        return $this;
    }

    public function aptconf($key, array $value)
    {
        $head = array(
            $key,
            '{',
        );
        $tail = array('}');
        $body = array_map(function ($v) {
            return '  "' . $v . '";';
        }, $value);
        $this->dest->appendToFileArray(array_merge($head, $body, $tail), self::APTCONF);
        return $this;
    }

    public function aptpref(array $pref)
    {
        $v = array_map(function ($k, $v) {
            return "$k: $v";
        }, array_keys($pref), $pref);
        $this->dest->appendToFileArray($v, self::APTPREF);
        return $this;
    }

    public function debconf($data)
    {
        $this->dest->shell(sprintf(
            'echo %s|debconf-set-selections',
            escapeshellarg($data)
        ));
        return $this;
    }

    public function reconf($pkg)
    {
        $this->dest->shell(sprintf(
            'DEBIAN_FRONTEND=noninteractive dpkg-reconfigure %s',
            $pkg
        ));
        return $this;
    }

    public function ensureBash()
    {
        $this->debconf('dash dash/sh boolean false');
        $this->reconf('dash');
        return $this;
    }
    
    public function export()
    {
        return $this->dest;
    }
}