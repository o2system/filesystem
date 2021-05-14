<?php


namespace Tests;


use O2System\Filesystem\Exception\IOException;
use O2System\Filesystem\File;

class FileTest extends FileSystemTestCase
{

    public function testCopyCreatesNewFile()
    {
        $sourceFilePath = $this->workspace.\DIRECTORY_SEPARATOR.'copy_source_file';
        $targetFilePath = $this->workspace.\DIRECTORY_SEPARATOR.'copy_target_file';

        file_put_contents($sourceFilePath, 'SOURCE FILE');
        $file = new File($sourceFilePath);

        $file->copy($targetFilePath);

        $this->assertFileExists($targetFilePath);
        $this->assertStringEqualsFile($targetFilePath, 'SOURCE FILE');
    }

    public function testCopyUnreadableFileFails()
    {
        $this->expectException(IOException::class);
        // skip test on Windows; PHP can't easily set file as unreadable on Windows
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $this->markTestSkipped('This test cannot run on Windows.');
        }

        if (!getenv('USER') || 'root' === getenv('USER')) {
            $this->markTestSkipped('This test will fail if run under superuser');
        }

        $sourceFilePath = $this->workspace.\DIRECTORY_SEPARATOR.'copy_source_file';
        $targetFilePath = $this->workspace.\DIRECTORY_SEPARATOR.'copy_target_file';

        file_put_contents($sourceFilePath, 'SOURCE FILE');

        $file = new File($sourceFilePath);

        // make sure target cannot be read
        $file->setMode(0222);

        $file->copy($targetFilePath);
    }


    public function testCopyOverridesExistingFileIfModified()
    {
        $sourceFilePath = $this->workspace.\DIRECTORY_SEPARATOR.'copy_source_file';
        $targetFilePath = $this->workspace.\DIRECTORY_SEPARATOR.'copy_target_file';

        file_put_contents($sourceFilePath, 'SOURCE FILE');
        file_put_contents($targetFilePath, 'TARGET FILE');
        touch($targetFilePath, time() - 1000);

        $file = new File($sourceFilePath);
        $file->copy($targetFilePath);

        $this->assertFileExists($targetFilePath);
        $this->assertStringEqualsFile($targetFilePath, 'SOURCE FILE');
    }
    public function testRenameFile()
    {
        $sourceFilePath = $this->workspace.\DIRECTORY_SEPARATOR.'file';
        $newPath = $this->workspace.\DIRECTORY_SEPARATOR.'new_file';
        touch($sourceFilePath);

        $file = new File($sourceFilePath);
        $file->rename('new_file');

        $this->assertFileDoesNotExist($file);
        $this->assertFileExists($newPath);
    }

    public function testRenameOverwritesTheTargetIfItAlreadyExists()
    {
        $file = $this->workspace.\DIRECTORY_SEPARATOR.'file';
        $newPath = $this->workspace.\DIRECTORY_SEPARATOR.'new_file';

        touch($file);
        touch($newPath);

        $file = new File($file);
        $file->rename('new_file');

        $this->assertFileDoesNotExist($file);
        $this->assertFileExists($newPath);
    }

    public function testDeleteFile()
    {
        $filePath = $this->workspace.\DIRECTORY_SEPARATOR.'delete_source_file';

        file_put_contents($filePath, 'SOURCE FILE');

        $file = new File($filePath);
        $file->delete();

        $this->assertFileDoesNotExist($filePath);
    }

    public function testChmodChangesFileMode()
    {
        $this->markAsSkippedIfChmodIsMissing();

        $dir = $this->workspace.\DIRECTORY_SEPARATOR.'dir';
        mkdir($dir);
        $file = $dir.\DIRECTORY_SEPARATOR.'file';
        touch($file);

        $file = new File($file);

        $file->setMode(0400);
        $file->setMode( 0753);

        $this->assertFilePermissions(753, $dir);
        $this->assertFilePermissions(400, $file);
    }

    public function testChownByName()
    {
        $this->markAsSkippedIfPosixIsMissing();

        $dir = $this->workspace.\DIRECTORY_SEPARATOR.'dir';
        mkdir($dir);

        $owner = new File($dir);
        $owner->setOwner($owner);

        $this->assertSame($owner, $this->getFileOwner($dir));
    }



    public function testWriteEmptyFile()
    {
        $sourceFilePath = $this->workspace.\DIRECTORY_SEPARATOR.'1';

        $file = new File($sourceFilePath);
        $file->write($sourceFilePath, '1');

        $this->assertFileExists($file);
    }

    public function testWriteFile()
    {
        $filename = $this->workspace.\DIRECTORY_SEPARATOR.'foo'.\DIRECTORY_SEPARATOR.'baz.txt';

        // skip mode check on Windows
        if ('\\' !== \DIRECTORY_SEPARATOR) {
            $oldMask = umask(0002);
        }

        $file = new File($filename);

        $file->write($filename, 'bar');
        $this->assertFileExists($filename);
        $this->assertStringEqualsFile($filename, 'bar');

        // skip mode check on Windows
        if ('\\' !== \DIRECTORY_SEPARATOR) {
            $this->assertFilePermissions(664, $filename);
            umask($oldMask);
        }
    }

    public function testDumpFileOverwritesAnExistingFile()
    {
        $filename = $this->workspace.\DIRECTORY_SEPARATOR.'foo.txt';
        file_put_contents($filename, 'FOO BAR');

        $file = new File($filename);
        $file->write($filename, 'bar');

        $this->assertFileExists($filename);
        $this->assertStringEqualsFile($filename, 'bar');
    }

    public function testDumpFileWithFileScheme()
    {
        $scheme = 'file://';
        $filename = $scheme.$this->workspace.\DIRECTORY_SEPARATOR.'foo'.\DIRECTORY_SEPARATOR.'baz.txt';

        $file = new File($filename);
        $file->write($filename, 'bar');

        $this->assertFileExists($filename);
        $this->assertStringEqualsFile($filename, 'bar');
    }

    public function testDumpFileWithZlibScheme()
    {
        $scheme = 'compress.zlib://';
        $filename = $this->workspace.\DIRECTORY_SEPARATOR.'foo'.\DIRECTORY_SEPARATOR.'baz.txt';

        $file = new File($filename);
        $file->write($filename, 'bar');

        // Zlib stat uses file:// wrapper so remove scheme
        $this->assertFileExists(str_replace($scheme, '', $filename));
        $this->assertStringEqualsFile($filename, 'bar');
    }

    /**
     * Normalize the given path (transform each forward slash into a real directory separator).
     */
    private function normalize(string $path): string
    {
        return str_replace('/', \DIRECTORY_SEPARATOR, $path);
    }



}