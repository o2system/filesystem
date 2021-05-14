<?php


namespace O2System\Filesystem\Exception;


/**
 * Class IOException
 * @package O2System\Filesystem\Exception
 */
class IOException extends \RuntimeException implements IOExceptionInterface
{
    /**
     * @var string|null
     */
    private $path;

    /**
     * IOException constructor.
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     * @param string|null $path
     */
    public function __construct(string $message, int $code = 0, \Throwable $previous = null, string $path = null)
    {
        $this->path = $path;

        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->path;
    }

}