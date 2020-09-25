<?php

namespace Tests;

use O2System\Filesystem\File;
use PHPUnit\Framework\TestCase;

class FileSystemTestCase extends TestCase
{
    /**
     * @var int
     */
    private $umask;

    /**
     * @var array
     */
    protected array $longPathNamesWindows = [];

    /**
     * @var
     */
    protected $filesystem = null;

    /**
     * @var string|null
     */
    protected ?string $workspace = null;

    /**
     * @var bool|null Flag for hard links on Windows
     */
    private static $linkOnWindows = null;

    /**
     * @var bool|null Flag for symbolic links on Windows
     */
    private static $symlinkOnWindows = null;

    /**
     *
     */
    public static function setUpBeforeClass(): void
    {
        if ('\\' === \DIRECTORY_SEPARATOR) {
            self::$linkOnWindows = true;
            $originFile = tempnam(sys_get_temp_dir(), 'li');
            $targetFile = tempnam(sys_get_temp_dir(), 'li');
            if (true !== @link($originFile, $targetFile)) {
                $report = error_get_last();
                if (\is_array($report) && false !== strpos($report['message'], 'error code(1314)')) {
                    self::$linkOnWindows = false;
                }
            } else {
                @unlink($targetFile);
            }

            self::$symlinkOnWindows = true;
            $originDir = tempnam(sys_get_temp_dir(), 'sl');
            $targetDir = tempnam(sys_get_temp_dir(), 'sl');
            if (true !== @symlink($originDir, $targetDir)) {
                $report = error_get_last();
                if (\is_array($report) && false !== strpos($report['message'], 'error code(1314)')) {
                    self::$symlinkOnWindows = false;
                }
            } else {
                @unlink($targetDir);
            }
        }
    }

    /**
     *
     */
    protected function setUp(): void
    {
        $this->umask = umask(0);
        $this->filesystem = new File();
        $this->workspace = sys_get_temp_dir().'/'.microtime(true).'.'.mt_rand();
        mkdir($this->workspace, 0777, true);
        $this->workspace = realpath($this->workspace);
    }

    /**
     *
     */
    protected function tearDown(): void
    {
        if (!empty($this->longPathNamesWindows)) {
            foreach ($this->longPathNamesWindows as $path) {
                exec('DEL '.$path);
            }
            $this->longPathNamesWindows = [];
        }

        umask($this->umask);
    }

    /**
     * @param int    $expectedFilePerms Expected file permissions as three digits (i.e. 755)
     * @param string $filePath
     */
    protected function assertFilePermissions($expectedFilePerms, $filePath)
    {
        $actualFilePerms = (int) substr(sprintf('%o', fileperms($filePath)), -3);
        $this->assertEquals(
            $expectedFilePerms,
            $actualFilePerms,
            sprintf('File permissions for %s must be %s. Actual %s', $filePath, $expectedFilePerms, $actualFilePerms)
        );
    }

    /**
     * @param $filepath
     * @return mixed
     */
    protected function getFileOwnerId($filepath)
    {
        $this->markAsSkippedIfPosixIsMissing();

        $infos = stat($filepath);

        return $infos['uid'];
    }

    /**
     * @param $filepath
     * @return mixed|null
     */
    protected function getFileOwner($filepath)
    {
        $this->markAsSkippedIfPosixIsMissing();

        return ($datas = posix_getpwuid($this->getFileOwnerId($filepath))) ? $datas['name'] : null;
    }

    /**
     * @param $filepath
     * @return mixed
     */
    protected function getFileGroupId($filepath)
    {
        $this->markAsSkippedIfPosixIsMissing();

        $infos = stat($filepath);

        return $infos['gid'];
    }

    /**
     * @param $filepath
     * @return mixed
     */
    protected function getFileGroup($filepath)
    {
        $this->markAsSkippedIfPosixIsMissing();

        if ($datas = posix_getgrgid($this->getFileGroupId($filepath))) {
            return $datas['name'];
        }

        $this->markTestSkipped('Unable to retrieve file group name');
    }

    /**
     *
     */
    protected function markAsSkippedIfLinkIsMissing()
    {
        if (!\function_exists('link')) {
            $this->markTestSkipped('link is not supported');
        }

        if ('\\' === \DIRECTORY_SEPARATOR && false === self::$linkOnWindows) {
            $this->markTestSkipped('link requires "Create hard links" privilege on windows');
        }
    }

    /**
     * @param false $relative
     */
    protected function markAsSkippedIfSymlinkIsMissing($relative = false)
    {
        if ('\\' === \DIRECTORY_SEPARATOR && false === self::$symlinkOnWindows) {
            $this->markTestSkipped('symlink requires "Create symbolic links" privilege on Windows');
        }

        // https://bugs.php.net/69473
        if ($relative && '\\' === \DIRECTORY_SEPARATOR && 1 === PHP_ZTS) {
            $this->markTestSkipped('symlink does not support relative paths on thread safe Windows PHP versions');
        }
    }

    /**
     *
     */
    protected function markAsSkippedIfChmodIsMissing()
    {
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $this->markTestSkipped('chmod is not supported on Windows');
        }
    }

    /**
     *
     */
    protected function markAsSkippedIfPosixIsMissing()
    {
        if (!\function_exists('posix_isatty')) {
            $this->markTestSkipped('Function posix_isatty is required.');
        }
    }

}