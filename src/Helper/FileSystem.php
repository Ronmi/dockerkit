<?php

namespace Fruit\DockerKit\Helper;

trait FileSystem
{
    protected function escapePath($path)
    {
        $file = str_replace("\\", "\\\\", $path);
        $file = str_replace(' ', "\\ ", $file);
        $file = str_replace('"', "\\\"", $file);
        $file = str_replace("'", "\\'", $file);
        return $file;
    }

    /**
     * @return Dockerfile
     */
    public function chmods($perm, array $files, array $options = null)
    {
        $opts = '';
        if (is_array($options)) {
            $options = array_map('escapeshellarg', $options);
            $opts = ' ' . implode(' ', $options);
        }
        $files = array_map(array($this, 'escapePath'), $files);

        $cmd = sprintf(
            'chmod %s%s %s',
            $perm,
            $opts,
            implode(' ', $files)
        );
        return $this->shell($cmd);
    }

    /**
     * @return Dockerfile
     */
    public function chowns($owner, array $files, array $options = null)
    {
        $opts = '';
        if (is_array($options)) {
            $options = array_map('escapeshellarg', $options);
            $opts = ' ' . implode(' ', $options);
        }
        $files = array_map(array($this, 'escapePath'), $files);

        $cmd = sprintf(
            'chown %s%s %s',
            $owner,
            $opts,
            implode(' ', $files)
        );
        return $this->shell($cmd);
    }

    /**
     * @return Dockerfile
     */
    public function chmod($perm, $file, array $options = null)
    {
        return $this->chmods($perm, array($file), $options);
    }

    /**
     * @return Dockerfile
     */
    public function chown($owner, $file, array $options = null)
    {
        return $this->chowns($owner, array($file), $options);
    }

    /**
     * @return Dockerfile
     */
    public function symlink($src, $dest, array $opts = null)
    {
        return $this->symlinkArray(array($src), $dest, $opts);
    }

    /**
     * @return Dockerfile
     */
    public function symlinkArray(array $src, $dest, array $opts = null)
    {
        $args = '';
        if ($opts != null) {
            $args = ' ' . implode(' ', escapeshellarg($opts));
        }
        $src = array_map(array($this, 'escapePath'), $src);
        return $this->shell(sprintf(
            'ln -s%s %s %s',
            $args,
            implode(' ', $src),
            $this->escapePath($dest)
        ));
    }

    /**
     * @return Dockerfile
     */
    public function move($src, $dest, array $opts = null)
    {
        return $this->moveArray(array($src), $dest, $opts);
    }

    /**
     * @return Dockerfile
     */
    public function moveArray(array $src, $dest, array $opts = null)
    {
        $args = '';
        if ($opts != null) {
            $args = ' ' . implode(' ', escapeshellarg($opts));
        }
        $src = array_map(array($this, 'escapePath'), $src);
        return $this->shell(sprintf(
            'mv%s %s %s',
            $args,
            implode(' ', $src),
            $this->escapePath($dest)
        ));
    }
}
