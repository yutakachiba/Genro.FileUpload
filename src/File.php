<?php

/**
 * File.php
 *
 * @copyright Yutaka Chiba <yutakachiba@gmail.com>
 * @created   2015/02/22 13:26
 */
namespace Genro\FileUpload;

use Genro\FileUpload\Traits\FilesystemSetterTrait;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Class File
 *
 * @package Genro\FileUpload
 * @author  Yutaka Chiba <yutakachiba@gmail.com>
 *
 * @property-read string $file_path
 * @property-read string $mime_type
 * @property-read int    $file_size
 * @property-read int    $width
 * @property-read int    $height
 */
class File
{

    use FilesystemSetterTrait;

    /**
     * @var string
     */
    private $file_path;

    /**
     * @var string
     */
    private $mime_type;

    /**
     * @var int
     */
    private $file_size;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @param string $file_path
     * @param string $mime_type
     * @param int    $file_size
     * @param int    $width
     * @param int    $height
     */
    public function __construct(
        $file_path,
        $mime_type = null,
        $file_size = null,
        $width = null,
        $height = null
    ) {
        $this->file_path = $file_path;
        $this->mime_type = $mime_type;
        $this->file_size = $file_size;
        $this->width     = $width;
        $this->height    = $height;
    }

    /**
     * @return File
     */
    public function load()
    {
        if (!$this->filesystem->exists($this->file_path)) {
            throw new FileNotFoundException(
                sprintf('Failed to load "%s" because file does not exist.', $this->file_path)
            );
        }
        if (!is_file($this->file_path)) {
            throw new FileNotFoundException(
                sprintf('Failed to load "%s" because path is not a file.', $this->file_path)
            );
        }

        $mime_type = $this->getMimeType();
        $file_size = $this->getFileSize();
        list($width, $height) = $this->getWidthAndHeight($mime_type);

        return new File(
            $this->file_path,
            $mime_type,
            $file_size,
            $width,
            $height
        );
    }

    /**
     * @return string
     */
    protected function getMimeType()
    {
        $mimeTypeFrom = new \finfo(FILEINFO_MIME_TYPE);
        return $mimeTypeFrom->file($this->file_path);
    }

    /**
     * @return string
     */
    protected function getFileSize()
    {
        return filesize($this->file_path);
    }

    /**
     * @param string $mime_type
     * @return array
     */
    protected function getWidthAndHeight($mime_type)
    {
        $width = $height = null;

        if (in_array(
            $mime_type,
            [
                'image/gif',
                'image/jpeg',
                'image/png',
                'application/x-shockwave-flash',
            ]
        )) {
            list($width, $height) = getimagesize($this->file_path);
        }

        return [$width, $height];
    }

    /**
     * @param string $basePath
     * @return string
     */
    public function getRelativePath($basePath)
    {
        if (strpos($this->file_path, $basePath) !== 0) {
            throw new \LogicException();
        }

        return trim(substr($this->file_path, strlen($basePath)), '/');
    }

    /**
     * @param string $to
     * @param bool $overwrite
     * @return File
     */
    public function copy($to, $overwrite = false)
    {
        try {

            $this->filesystem->copy($this->file_path, $to, $overwrite);

            return new File(
                $to,
                $this->mime_type,
                $this->file_size,
                $this->width,
                $this->height
            );

        } catch (IOException $e) {

            throw $e;
        }
    }

    /**
     * @param string $to
     * @param bool $overwrite
     * @return File
     */
    public function move($to, $overwrite = false)
    {
        try {

            $this->filesystem->rename($this->file_path, $to, $overwrite);

            return new File(
                $to,
                $this->mime_type,
                $this->file_size,
                $this->width,
                $this->height
            );

        } catch (IOException $e) {

            throw $e;
        }
    }

    /**
     * @return void
     */
    public function remove()
    {
        $this->filesystem->remove($this->file_path);
    }

    /**
     * @param int $mode
     */
    public function chmod($mode)
    {
        $this->filesystem->chmod($this->file_path, $mode);
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
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        throw new \LogicException(
            sprintf('Not allowed to set the property. "%s::%s"', get_class($this), $name)
        );
    }
}
