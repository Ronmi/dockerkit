<?php

namespace Fruit\DockerKit\Dockerfile;

trait File
{
    /**
     * @return Dockerfile
     */
    public function appendToFile($content, $path)
    {
        return $this->appendToFileArray(explode("\n", $content), $path);
    }

    /**
     * @return Dockerfile
     */
    public function appendToFileArray(array $content, $path)
    {
        $tmpl = 'echo %s|tee -a %s';
        $merge = $this->grouping();
        $this->grouping(true);
        foreach ($content as $c) {
            $this->shell(
                sprintf(
                    $tmpl,
                    escapeshellarg($c),
                    $this->escapePath($path)
                )
            );
        }
        return $this->grouping($merge);
    }

    /**
     * @return Dockerfile
     */
    public function textfile($content, $path)
    {
        return $this->textfileArray(explode("\n", $content), $path);
    }

    /**
     * Create several textfiles at once.
     *
     * @return Dockerfile
     */
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

    /**
     * Must not have newline character, or command generated will go wrong.
     * @return Dockerfile
     */
    public function textfileArray(array $content, $path)
    {
        $file = $this->escapePath($path);
        $tmpl = 'echo %s|%s';
        $merge = $this->grouping();
        $this->grouping(true);
        $first = array_shift($content);
        $this->shell(sprintf($tmpl, escapeshellarg($first), 'tee ' . $file));

        foreach ($content as $line) {
            $this->shell(
                sprintf(
                    $tmpl,
                    escapeshellarg($line),
                    'tee -a ' . $file
                )
            );
        }
        return $this->grouping($merge);
    }

    /**
     * @return Dockerfile
     */
    public function textfileAs($content, $path, $user)
    {
        return $this
            ->uStart($user)
            ->textfile($content, $path)
            ->uEnd();
    }

    /**
     * @return Dockerfile
     */
    public function binaryfile($binaryString, $path)
    {
        $str = base64_encode($binaryString);
        return $this->shell(sprintf("echo '%s'|base64 -d > %s", $str, $this->escapePath($path)));
    }

    /**
     * @return Dockerfile
     */
    public function binaryfileAs($binaryString, $path, $user)
    {
        $str = base64_encode($binaryString);
        return $this
            ->uStart($user)
            ->shell(sprintf("echo '%s'|base64 -d > %s", $str, $this->escapePath($path)))
            ->uEnd();
    }
}
