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
    private $installedPackages;

    public function __construct(Dockerfile $dest)
    {
        $this->dest = $dest;
        $this->updated = false;
        $this->installedPackages = array();
    }

    private function inst1(array $packages)
    {
        $this->dest
            ->gStart(true)
            ->shell('apt-get install -y ' . implode(' ', $packages))
            ->shell('apt-get clean')
            ->gEnd();
        return $this;
    }

    private function inst2(array $packages, array $cmds)
    {
        $last = array_pop($cmds);
        if ($last != 'apt-get clean') {
            return $this->inst1($packages);
        }

        $last = array_pop($cmds);
        if (substr($last, 0, 19) != 'apt-get install -y ') {
            return $this->inst1($packages);
        }

        $last .= ' ' . implode(' ', $packages);
        $cmds[] = $last;
        $cmds[] = 'apt-get clean';
        $this->dest->lastNCommand(1, array($cmds));
        return $this;
    }

    /**
     * @return Debian
     */
    public function install(array $packages)
    {
        if (!$this->updated) {
            $this->dest->shell('apt-get update');
            $this->updated = true;
        }
        $packages = array_diff($packages, $this->installedPackages);
        if (count($packages) < 1) {
            return $this;
        }

        $this->installedPackages = array_merge($this->installedPackages, $packages);

        $res = $this->dest->lastNCommand();
        if (isset($res[0]) and is_array($res[0])) {
            return $this->inst2($packages, $res[0]);
        }

        return $this->inst1($packages);
    }

    private function addKeyByURI($uri)
    {
        $keyStr = file_get_contents($uri);
        $this->updated = false;
        return $this->addKeyByString($keyStr);
    }

    private function addKeyByString($key)
    {
        if ($key == '') {
            return $this;
        }
        $this->dest
            ->gStart(true)
            ->textfile($key, '/tmp/apt.key')
            ->shell('cat /tmp/apt.key|apt-keys add -')
            ->shell('rm /tmp/apt.key')
            ->gEnd();
        $this->updated = false;
        return $this;
    }

    private function addKeyByFingerprint($fingerprint, $server = null)
    {
        if (!$server) {
            $server = 'pgp.mit.edu';
        }
        $this->dest->shell("apt-key adv --recv-key $fingerprint --keyserver $server");
        $this->updated = false;
        return $this;
    }

    /**
     * @return Debian
     */
    public function repo(array $repos)
    {
        $repoLines = array_keys($repos);
        $this->dest->textfileArray($repoLines, self::SOURCES);
        $this->updated = false;

        foreach ($repos as $v) {
            if ($v == null) {
                continue;
            }

            if (is_array($v)) {
                $this->addKeyByFingerprint($v[0], $v[1]);
                continue;
            }
            if (ctype_xdigit($v)) {
                $this->addKeyByFingerprint($v);
                continue;
            }
            $this->addKeyByURI($v);
        }
        return $this;
    }

    private function aptconf($key, array $value)
    {
        $head = array(
            $key,
            '{',
        );
        $tail = array('}');
        $body = array_map(
            function ($v) {
                return '  "' . $v . '";';
            }, $value
        );
        $this->dest->appendToFileArray(array_merge($head, $body, $tail), self::APTCONF);
        return $this;
    }

    private function aptpref(array $pref)
    {
        $v = array_map(
            function ($k, $v) {
                return "$k: $v";
            }, array_keys($pref), $pref
        );
        $this->dest->appendToFileArray($v, self::APTPREF);
        return $this;
    }

    /**
     * @return Debian
     */
    public function pmsconf(array $config)
    {
        if (isset($config['conf'])) {
            foreach ($config['conf'] as $k => $v) {
                $this->aptconf($k, $v);
            }
        }

        if (isset($config['pref'])) {
            $this->aptpref($config['pref']);
        }

        return $this;
    }

    private function debconf($data)
    {
        $this->dest->shell(
            sprintf(
                'echo %s|debconf-set-selections',
                escapeshellarg($data)
            )
        );
        return $this;
    }

    private function reconf($pkg)
    {
        $this->dest->shell(
            sprintf(
                'DEBIAN_FRONTEND=noninteractive dpkg-reconfigure %s',
                $pkg
            )
        );
        return $this;
    }

    /**
     * @return Debian
     */
    public function pkgconf($pkg, $config = null)
    {
        if (isset($config['data'])) {
            $this->debconf($pkg . ' ' . $config['data']);
            if (isset($config['reconf'])) {
                $this->reconf($pkg);
            }
            return $this;
        }

        if ($config) {
            $this->debconf($pkg . ' ' . $config);
        }
        return $this->reconf($pkg);
    }

    /**
     * @return Debian
     */
    public function ensureBash()
    {
        $this->debconf('dash dash/sh boolean false');
        $this->reconf('dash');
        return $this;
    }

    /**
     * @return Debian
     */
    public function tz($tz)
    {
        $this->dest->textfile($tz, '/etc/timezone');
        $this->reconf('tzdata');
        return $this;
    }
}
