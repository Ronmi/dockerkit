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
    public function chmod($perm, array $files, array $options = null)
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
    public function chown($owner, array $files, array $options = null)
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
}
