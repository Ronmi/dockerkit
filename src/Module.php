<?php

namespace Fruit\DockerKit;

interface Module
{
    /// return a Dockerfile instance
    public function export();
}