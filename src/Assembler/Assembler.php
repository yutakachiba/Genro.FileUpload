<?php

/**
 * Assembler.php
 *
 * @copyright Yutaka Chiba <yutakachiba@gmail.com>
 * @created   2015/02/21 8:25
 */
namespace Genro\FileUpload\Assembler;

use Genro\FileUpload\Paths;
use Genro\FileUpload\Traits\FilesystemSetterTrait;

/**
 * Class Assembler
 *
 * @package Genro\FileUpload\Assembler
 * @author  Yutaka Chiba <yutakachiba@gmail.com>
 */
class Assembler implements AssemblerInterface
{

    use FilesystemSetterTrait;

    /**
     * @var Paths
     */
    protected $paths;

    /**
     * @var string
     */
    protected $targetDir;

    /**
     * @var array
     */
    protected $filePaths;

    /**
     * @var string
     */
    protected $transactionId;

    /**
     * @var string
     */
    protected $absWorkDir;

    /**
     * @param Paths  $paths
     * @param string $targetDir
     * @param array  $filePaths
     */
    public function __construct(Paths $paths, $targetDir, array $filePaths)
    {
        $this->paths      = $paths;
        $this->targetDir  = $targetDir;
        $this->filePaths  = $filePaths;
        $this->absWorkDir = $this->getAbsWorkDir();
    }

    /**
     * @return void
     */
    public function assemble()
    {
        // Create a working directory.
        $this->createWorkDir();

        // Copy / Rename files into the working directory.
        foreach ($this->filePaths as $filePath) {

            if ($this->isNewlyUploadedFile($filePath)) {

                // Rename a newly upload file.
                $from = $this->paths->rootDir . $filePath;
                $to   = $this->absWorkDir . '/' . basename($filePath);
                $this->filesystem->rename($from, $to, ($overwrite = false));
                $this->filesystem->chmod($to, 0666);
                continue;

            } elseif ($this->isAlreadyUploadedFile($filePath)) {

                // Copy an already uploaded file.
                $from = $this->paths->rootDir . $filePath;
                $to   = $this->absWorkDir . '/' . basename($filePath);
                $this->filesystem->copy($from, $to, ($overwrite = false));
                $this->filesystem->chmod($to, 0666);
                continue;
            }

            throw new \RuntimeException(
                'Unexpected media directory specified.'
            );
        }

        if ($filesystem->exists($absSaveDir)) {
            $from = $absSaveDir;
            $to   = $absSaveDir . '_BK' . $transactionId;
            $filesystem->rename($from, $to);
        }

        $from = $absWorkDir;
        $to   = $absSaveDir;
        $filesystem->rename($from, $to);
    }

    /**
     * @return string
     */
    protected function getTransactionId()
    {
        $shortHash = function ($data, $algo = 'CRC32') {
            return strtr(rtrim(base64_encode(pack('H*', sprintf('%u', $algo($data)))), '='), '+/', '-_');
        };

        return sprintf('%s_%s', date('Ymd_His'), $shortHash(uniqid()));
    }

    /**
     * @return string
     */
    protected function getAbsWorkDir()
    {
        return sprintf(
            '%s/genro_fileupload_assembler/%s/%s',
            $this->paths->getAbsTmpDir(),
            ltrim($this->paths->saveDir . '/'),
            ltrim($this->targetDir)
        );
    }

    /**
     * @return void
     */
    protected function createWorkDir()
    {
        $this->filesystem->mkdir($this->absWorkDir);
    }

    /**
     * @param string $filePath
     * @return bool
     */
    protected function isNewlyUploadedFile($filePath)
    {
        return strpos($filePath, $this->paths->tmpDir) === 0;
    }

    /**
     * @param string $filePath
     * @return bool
     */
    protected function isAlreadyUploadedFile($filePath)
    {
        return strpos($filePath, $this->paths->saveDir) === 0;
    }
}
