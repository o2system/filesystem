<?php


namespace O2System\Filesystem\Exception;


/**
 * Class FileNotFoundException
 * @package O2System\Filesystem\Exception
 */
class FileNotFoundException extends IOException
{
    /**
     * FileNotFoundException constructor.
     * @param string|null $message
     * @param int $code
     * @param \Throwable|null $previous
     * @param string|null $path
     */
    public function __construct(string $message = null, int $code = 0, \Throwable $previous = null, string $path = null)
    {
        if (null === $message) {
            if (null === $path) {
                $message = 'File could not be found.';
            } else {
                $message = sprintf('File "%s" could not be found.', $path);
            }
        }

        parent::__construct($message, $code, $previous, $path);
    }

}