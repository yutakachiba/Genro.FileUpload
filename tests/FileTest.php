<?php

/**
 * FileTest.php
 *
 * @copyright Yutaka Chiba <yutakachiba@gmail.com>
 * @created   2015/02/22 14:07
 */
namespace Genro\FileUpload;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Class FileTest
 *
 * @package Genro\FileUpload
 * @author  Yutaka Chiba <yutakachiba@gmail.com>
 */
class FileTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var File
     */
    protected $file;

    protected function setUp()
    {
        $this->file = $this->newFile('/path/to/file.jpg', 'image/jpeg', 102400, 300, 200);
    }

    protected function newFile($file_path, $mime_type = null, $file_size = null, $width = null, $height = null)
    {
        $file = new File($file_path, $mime_type, $file_size, $width, $height);
        $file->setFilesystem(new Filesystem());
        return $file;
    }

    public function testNew()
    {
        $this->assertInstanceOf('\Genro\FileUpload\File', $this->file);
        $this->assertSame('/path/to/file.jpg', $this->file->file_path);
        $this->assertSame('image/jpeg', $this->file->mime_type);
        $this->assertSame(102400, $this->file->file_size);
        $this->assertSame(300, $this->file->width);
        $this->assertSame(200, $this->file->height);
    }

    public function testLoad()
    {
        // txt
        $txt = $this->newFile(__DIR__ . '/files/test.txt');
        $txt = $txt->load();

        $this->assertSame(__DIR__ . '/files/test.txt', $txt->file_path);
        $this->assertSame('text/plain', $txt->mime_type);
        $this->assertSame(4, $txt->file_size);
        $this->assertNull($txt->width);
        $this->assertNull($txt->height);

        // gif
        $gif = $this->newFile(__DIR__ . '/files/test.gif');
        $gif = $gif->load();

        $this->assertSame(__DIR__ . '/files/test.gif', $gif->file_path);
        $this->assertSame('image/gif', $gif->mime_type);
        $this->assertSame(4204, $gif->file_size);
        $this->assertSame(420, $gif->width);
        $this->assertSame(80, $gif->height);

        // png
        $png = $this->newFile(__DIR__ . '/files/test.png');
        $png = $png->load();

        $this->assertSame(__DIR__ . '/files/test.png', $png->file_path);
        $this->assertSame('image/png', $png->mime_type);
        $this->assertSame(4151, $png->file_size);
        $this->assertSame(60, $png->width);
        $this->assertSame(60, $png->height);

        // jpg
        $jpg = $this->newFile(__DIR__ . '/files/test.jpg');
        $jpg = $jpg->load();

        $this->assertSame(__DIR__ . '/files/test.jpg', $jpg->file_path);
        $this->assertSame('image/jpeg', $jpg->mime_type);
        $this->assertSame(212967, $jpg->file_size);
        $this->assertSame(800, $jpg->width);
        $this->assertSame(540, $jpg->height);

        // dummy (same file of test.jpg)
        $dmy = $this->newFile(__DIR__ . '/files/test.dummy');
        $dmy = $dmy->load();

        $this->assertSame(__DIR__ . '/files/test.dummy', $dmy->file_path);
        $this->assertSame('image/jpeg', $dmy->mime_type);
        $this->assertSame(212967, $dmy->file_size);
        $this->assertSame(800, $dmy->width);
        $this->assertSame(540, $dmy->height);
    }

    /**
     * @expectedException \Symfony\Component\Filesystem\Exception\FileNotFoundException
     */
    public function testLoadFileNotFoundException()
    {
        $this->file->load();
    }

    /**
     * @expectedException \Symfony\Component\Filesystem\Exception\FileNotFoundException
     */
    public function testLoadFileInvalidFileTypeException()
    {
        $file = $this->newFile(__DIR__);
        $file->load();
    }

    public function testGetRelativePath()
    {
        $path = $this->file->getRelativePath('/path');
        $this->assertSame('to/file.jpg', $path);

        $path = $this->file->getRelativePath('/path/');
        $this->assertSame('to/file.jpg', $path);
    }

    public function testCopy()
    {
        $from = __DIR__ . '/files/test.txt';
        $to   = __DIR__ . '/tmp/test.txt';

        if (file_exists($to)) {
            unlink($to);
        }

        $this->assertFileNotExists($to);

        $file = $this->newFile($from);
        $file->copy($to);

        $this->assertFileExists($to);
        unlink($to);
    }

    /**
     * @expectedException \Symfony\Component\Filesystem\Exception\IOException
     */
    public function testCopyException()
    {
        $from = __DIR__ . '/files/test.txt';
        $to   = '/UNKNOWN/test.txt';

        $this->assertFileNotExists(dirname($to));

        $file = $this->newFile($from);
        $file->copy($to);
    }

    public function testMove()
    {
        $from = __DIR__ . '/files/' . date('YmdHis') . '.txt';
        $to   = __DIR__ . '/tmp/' . date('YmdHis') . '.txt';

        touch($from);

        if (file_exists($to)) {
            unlink($to);
        }

        $this->assertFileExists($from);
        $this->assertFileNotExists($to);

        $file = $this->newFile($from);
        $file->move($to);

        $this->assertFileNotExists($from);
        $this->assertFileExists($to);
        unlink($to);
    }

    /**
     * @expectedException \Symfony\Component\Filesystem\Exception\IOException
     */
    public function testMoveException()
    {
        $from = __DIR__ . '/files/test.txt';
        $to   = '/UNKNOWN/test.txt';

        $this->assertFileNotExists(dirname($to));

        $file = $this->newFile($from);
        $file->move($to);
    }

    public function testRemove()
    {
        $filePath = __DIR__ . '/tmp/' . date('YmdHis') . '.txt';
        touch($filePath);

        $this->assertFileExists($filePath);

        $file = $this->newFile($filePath);
        $file->remove();

        $this->assertFileNotExists($filePath);
    }

    public function testChmod()
    {
        $filePath = __DIR__ . '/tmp/' . date('YmdHis') . '.txt';
        touch($filePath);

        clearstatcache($filePath);
        $perms = substr(sprintf('%o', fileperms($filePath)), -4);
        $this->assertSame($perms, '0644');

        $file = $this->newFile($filePath);
        $file->chmod(0666);

        clearstatcache($filePath);
        $perms = substr(sprintf('%o', fileperms($filePath)), -4);
        $this->assertSame($perms, '0666');
        unlink($filePath);

    }

    /**
     * @expectedException \LogicException
     */
    public function testGetRelativePathException()
    {
        $this->file->getRelativePath('/invalid/path');
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Not allowed to get the property. "Genro\FileUpload\File::unknown"
     */
    public function testGetException()
    {
        $this->file->unknown;
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Not allowed to set the property. "Genro\FileUpload\File::file_path"
     */
    public function testSetException()
    {
        $this->file->file_path = __FILE__;
    }
}
