<?php


namespace Tests;


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
}