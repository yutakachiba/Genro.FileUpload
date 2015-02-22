<?php

/**
 * Paths.php
 *
 * @copyright Yutaka Chiba <yutakachiba@gmail.com>
 * @created   2015/02/21 8:27
 */
namespace Genro\FileUpload;

/**
 * Class Paths
 *
 * @package Genro\FileUpload
 * @author  Yutaka Chiba <yutakachiba@gmail.com>
 *
 * @property-read string $rootDir
 * @property-read string $saveDir
 * @property-read string $tmpDir
 */
class Paths
{

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var string
     */
    private $saveDir;

    /**
     * @var string
     */
    private $tmpDir;


    /**
     * @param string $rootDir
     * @param string $saveDir
     * @param string $tmpDir
     */
    public function __construct($rootDir, $saveDir, $tmpDir)
    {
        $this->rootDir = $this->normalizePath($rootDir);
        $this->saveDir = $this->normalizePath($saveDir);
        $this->tmpDir  = $this->normalizePath($tmpDir);
    }

    /**
     * @param string $path
     * @return string
     */
    protected function normalizePath($path)
    {
        $path = (string)$path;
        $path = trim($path, '/');
        return '/' . $path;
    }

    /**
     * @return string
     */
    public function getAbsSaveDir()
    {
        return $this->rootDir . $this->saveDir;
    }

    /**
     * @return string
     */
    public function getAbsTmpDir()
    {
        return $this->rootDir . $this->tmpDir;
    }

    /**
     * Read-only access to properties.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (!property_exists($this, $name)) {
            throw new \LogicException(
                sprintf('Not allowed to get the property. "%s::%s"', get_class($this), $name)
            );
        }

        return $this->{$name};
    }

    /**
     * Read-only access to properties.
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if (!property_exists($this, $name)) {
            throw new \LogicException(
                sprintf('Not allowed to set the property. "%s::%s"', get_class($this), $name)
            );
        }

        $this->{$name}  =$value;
    }
}
