<?php


namespace O2System\Filesystem\Exception;


class IOException extends \RuntimeException implements IOExceptionInterface
{
    private $path;

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