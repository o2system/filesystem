<?php


namespace O2System\Filesystem\Handlers;


use O2System\Filesystem\File;

class Stream
{
    /**
     * Stream::$resource
     *
     * @var resource
     */
    protected $resource;

    /**
     * Stream::$pointer
     *
     * @var int
     */
    protected $pointer;

    // ------------------------------------------------------------------------

    /**
     * Stream::__construct
     *
     * @param File   $file
     * @param string $mode
     */
    public function __construct(File $file, string $mode = 'rb')
    {
        if(is_file($file->getRealPath())) {
            $this->resource = fopen($file->getRealPath(), $mode);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Stream::setPointer
     *
     * @param int $point
     * @return static
     */
    public function setPointer(int $point): self
    {
        $this->pointer = $point;
        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Stream::getPointer
     *
     * @return int
     */
    public function getPointer(): int
    {
        return $this->pointer;
    }

    // ------------------------------------------------------------------------

    /**
     * Stream::getResource
     *
     * @return false|resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    // ------------------------------------------------------------------------

    /**
     * Stream::forward
     *
     * @param int $length
     */
    public function forward(int $length)
    {
        $this->pointer += $length;
        fseek($this->resource, $this->pointer);
    }

    //-------------------------------------------------------

    /**
     * Stream::rewind
     *
     * @param int $length
     */
    public function rewind(int $length)
    {
        $this->pointer -= $length;
        fseek($this->resource, $this->pointer);
    }

    //-------------------------------------------------------

    /**
     * Stream::readByteOfData
     *
     * @param $length
     * @return false|string
     */
    public function readByteOfData($length)
    {
        $data = fread($this->resource, $length);
        $this->pointer += $length;
        return $data;
    }

    //-------------------------------------------------------

    /**
     * Stream::readByteInteger
     *
     * @return int
     */
    public function readByteInteger(): int
    {
        $data = fread($this->resource, 1);
        $this->pointer++;
        return ord($data);
    }

    //-------------------------------------------------------

    /**
     * Stream::isValidByte
     *
     * @param string $byte
     * @return bool
     */
    public function isValidByte(string $byte): bool
    {
        if (fgetc($this->resource) == chr($byte)) {
            fseek($this->resource, $this->pointer);
            return true;
        } else {
            fseek($this->resource, $this->pointer);
            return false;
        }
    }

    //-------------------------------------------------------

    /**
     * Stream::isEndOfFile
     *
     * @return bool
     */
    public function isEndOfFile(): bool
    {
        if (fgetc($this->resource) === false) {
            return true;
        } else {
            fseek($this->resource, $this->pointer);
            return false;
        }
    }
    //-------------------------------------------------------

    /**
     * Stream::close
     */
    public function close()
    {
        if(is_resource($this->resource)) {
            fclose($this->resource);
        }
    }
}