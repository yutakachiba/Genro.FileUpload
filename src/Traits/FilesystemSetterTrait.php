<?php

/**
 * FilesystemSetterTrait.php
 *
 * @copyright Yutaka Chiba <yutakachiba@gmail.com>
 * @created 2015/02/10 21:06
 */
namespace Genro\FileUpload\Traits;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Trait FilesystemSetterTrait
 *
 * @package Genro\FileUpload\Traits
 * @author Yutaka Chiba <yutakachiba@gmail.com>
 */
trait FilesystemSetterTrait
{

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param Filesystem $filesystem
     */
    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }
}
