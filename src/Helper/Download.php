<?php

namespace Fruit\DockerKit\Helper;

trait Download
{
    /**
     * @return Dockerfile
     */
    public function wget($uri, $path = '', array $options = null)
    {
        $args = '';
        if ($options !== null) {
            $args = implode(' ', array_map(function ($val) {
                return escapeshellarg($val);
            }, $options)) . ' ';
        }
        if (!$path) {
            $path = basename($uri);
        }
        $path = $this->escapePath($path);
        return $this->shell(sprintf('wget %s-O %s %s', $args, $path, escapeshellarg($uri)));
    }

    /**
     * @return Dockerfile
     */
    public function curl($uri, $path = '', array $options = null)
    {
        $args = '';
        if ($options !== null) {
            $args = implode(' ', array_map(function ($val) {
                return escapeshellarg($val);
            }, $options)) . ' ';
        }
        if (!$path) {
            $path = basename($uri);
        }
        $path = $this->escapePath($path);
        return $this->shell(sprintf('curl %s-o %s %s', $args, $path, escapeshellarg($uri)));
    }

    /**
     * @return Dockerfile
     */
    public function download($uri, $path = '')
    {
        if (!$path) {
            $path = basename($uri);
        }
        $this->binaryfile(file_get_contents($uri), $path);
    }
}
