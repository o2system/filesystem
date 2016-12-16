<?php
/**
 * This file is part of the O2System PHP Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author         Steeve Andrian Salim
 * @copyright      Copyright (c) Steeve Andrian Salim
 */
// ------------------------------------------------------------------------

namespace O2System\Filesystem\Handlers;

// ------------------------------------------------------------------------

/**
 * Class Download
 *
 * @package O2System\Filesystem\Handlers
 */
class Download
{
    /**
     * Download::$filePath
     *
     * The file path to be downloaded.
     *
     * @var string
     */
    protected $filePath = null;

    /**
     * Download::$data
     *
     * The data to be downloaded.
     *
     * @var string
     */
    protected $data = false;

    /**
     * Download::$mime
     *
     * Download file mime type.
     *
     * @var string
     */
    protected $mime = 'application/octet-stream';

    /**
     * Download::$speed
     *
     * Download speed limit.
     *
     * @var int
     */
    protected $speed = 0;

    /**
     * Download::$partialEnabled
     *
     * Download file partial enabled flag.
     *
     * @var bool
     */
    public $partialEnabled = false;

    // ------------------------------------------------------------------------

    /**
     * Download::__construct
     *
     * @param string|null $filePath The file to be downloaded.
     */
    public function __construct ( $filePath = null )
    {
        if ( ! is_null( $filePath ) ) {
            $this->setFilePath( $filePath );
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Download::setFilePath
     *
     * Sets file to be downloaded.
     *
     * @param   string $filePath The file path to be downloaded.
     *
     * @return  static
     */
    public function setFilePath ( $filePath )
    {
        $this->filePath = $filePath;

        return $this;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Download::setData
     *
     * Sets the data to be downloaded.
     *
     * @param string $data Data to be downloaded.
     *
     * @return static
     */
    public function setData ( $data )
    {
        $this->data = $data;

        return $this;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Download::setSpeed
     *
     * Sets download speed.
     *
     * @param int $speed Download speed limit.
     *
     * @return static
     */
    public function setSpeed ( $speed = 0 )
    {
        $this->speed = $speed;

        return $this;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Download::setMime
     *
     * Sets downloaded file mime type.
     *
     * @param   string $mime The downloaded file mime type.
     *
     * @return static
     */
    public function setMime ( $mime )
    {
        $this->mime = $mime;

        return $this;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Download::process
     *
     * Force download the file or data
     *
     * @return bool
     */
    public function process ()
    {
        if ( $this->filePath === '' OR $this->data === '' ) {
            return false;
        }

        if ( ! ( $this->data ) ) {
            $fileSize = strlen( $this->data );
        }

        if ( ! ( $this->data ) ) {
            if ( ! is_file( $this->filePath ) && ( $fileSize = filesize( $this->filePath ) ) === false ) {
                return false;
            }

            $filePath = $this->filePath;
            $filename = explode( '/', str_replace( DIRECTORY_SEPARATOR, '/', $this->filePath ) );
            $filename = end( $filename );

            $fileSize = filesize( $filePath );
        }

        $x = explode( '.', $filePath );
        $extension = pathinfo( $filePath, PATHINFO_EXTENSION );

        /* It was reported that browsers on Android 2.1 (and possibly older as well)
         * need to have the filename extension upper-cased in order to be able to
         * download it.
         *
         * Reference: http://digiblog.de/2011/04/19/android-and-the-download-file-headers/
         */
        if ( count( $x ) !== 1 AND
             isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) AND
             preg_match(
                 '/Android\s(1|2\.[01])/',
                 $_SERVER[ 'HTTP_USER_AGENT' ]
             )
        ) {
            $x[ count( $x ) - 1 ] = strtoupper( $extension );
            $filename = implode( '.', $x );
        }

        // Clean output buffer
        if ( ob_get_level() !== 0 && @ob_end_clean() === false ) {
            @ob_clean();
        }

        // Check for partial download
        if ( isset( $_SERVER[ 'HTTP_RANGE' ] ) AND
             $this->partialEnabled === true
        ) {
            list ( $a, $range ) = explode( "=", $_SERVER[ 'HTTP_RANGE' ] );
            list ( $fbyte, $lbyte ) = explode( "-", $range );

            if ( ! $lbyte ) {
                $lbyte = $fileSize - 1;
            }

            $newLength = $lbyte - $fbyte;

            header( "HTTP/1.1 206 Partial Content", true );
            header( "Content-Length: $newLength", true );
            header( "Content-Range: bytes $fbyte-$lbyte/$fileSize", true );
        } else {
            header( "Content-Length: " . $fileSize );
        }

        // Common headers
        header( 'Content-Type: ' . $this->mime, true );
        header( 'Content-Disposition: attachment; filename="' . pathinfo( $filename, PATHINFO_BASENAME ) . '"', true );

        $expires = 604800; // (60*60*24*7)
        header( 'Expires:' . gmdate( 'D, d M Y H:i:s', time() + $expires ) . ' GMT' );

        header( 'Accept-Ranges: bytes', true );
        header( "Cache-control: private", true );
        header( 'Pragma: private', true );

        // Open file
        if ( $this->data === false ) {
            $file = fopen( $filePath, 'r' );
            if ( ! $file ) {
                return false;
            }
        }

        // Cut data for partial download
        if ( isset( $_SERVER[ 'HTTP_RANGE' ] ) AND
             $this->partialEnabled === true
        ) {
            if ( $this->data === false ) {
                fseek( $file, $range );
            } else {
                $data = substr( $this->data, $range );
            }
        }

        // Disable script time limit
        @set_time_limit( 0 );

        // Check for speed limit or file optimize
        if ( $this->speed > 0 OR $this->data === false ) {
            if ( $this->data === false ) {
                $chunkSize = $this->speed > 0 ? $this->speed * 1024 : 512 * 1024;
                while ( ! feof( $file ) and ( connection_status() == 0 ) ) {
                    $buffer = fread( $file, $chunkSize );
                    echo $buffer;
                    flush();
                    if ( $this->speed > 0 ) {
                        sleep( 1 );
                    }
                }
                fclose( $this->file );
            } else {
                $index = 0;
                $this->speed *= 1024; //convert to kb
                while ( $index < $fileSize and ( connection_status() == 0 ) ) {
                    $left = $fileSize - $index;
                    $buffer_size = min( $left, $this->speed );
                    $buffer = substr( $this->data, $index, $buffer_size );
                    $index += $buffer_size;
                    echo $buffer;
                    flush();
                    sleep( 1 );
                }
            }
        } else {
            echo $this->data;
        }
    }
}