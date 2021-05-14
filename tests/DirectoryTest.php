<?php
namespace Tests;

use O2System\Filesystem\Directory;

class DirectoryTest extends FileSystemTestCase
{
    public function testMkdirCreatesDirectoriesRecursively()
    {
        $directory = $this->workspace
            .\DIRECTORY_SEPARATOR.'directory'
            .\DIRECTORY_SEPARATOR.'sub_directory';

        $directory = new Directory($directory);

        $directory->make($directory);

        $this->assertDirectoryExists($directory);
    }


    public function testRemoveDirectoriesIteratively()
    {
        $basePath = $this->workspace.\DIRECTORY_SEPARATOR.'directory'.\DIRECTORY_SEPARATOR;

        mkdir($basePath);
        mkdir($basePath.'dir');
        touch($basePath.'file');

        $directory = new Directory($basePath);

        $directory->delete();

        $this->assertFileDoesNotExist($basePath);
    }

    public function testChownById()
    {
        $this->markAsSkippedIfPosixIsMissing();

        $dir = $this->workspace.\DIRECTORY_SEPARATOR.'dir';
        mkdir($dir);

        $ownerId = new Directory($dir);
        $ownerId->setOwner($ownerId);

        $this->assertSame($ownerId, $this->getFileOwnerId($dir));
    }



}