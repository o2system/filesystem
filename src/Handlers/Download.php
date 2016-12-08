<?php
/**
 * git
 *
 * @author      Steeve Andrian Salim
 * @created     06/10/2016 11:22
 * @copyright   Copyright (c) 2016 Steeve Andrian Salim
 */

namespace O2System\Filesystem\Handlers;


class Download
{
    protected $filename       = null;

    protected $data           = false;

    protected $mime           = 'application/octet-stream';

    protected $speed          = 0;

    protected $partialEnabled = false;

    public function __construct ( $filename = null )
    {
        if ( ! is_null( $filename ) ) {
            $this->setFile( $filename );
        }
    }

    /**
     * Set filename
     *
     * @param string $filename
     */
    public function setFile ( $filename )
    {
        $this->filename = $filename;

        return $this;
    }

    // --------------------------------------------------------------------------------------

    public function setData ( $data )
    {
        $this->data = $data;
    }

    // --------------------------------------------------------------------------------------

    public function setSpeed ( $speed = 0 )
    {
        $this->speed = $speed;

        return $this;
    }

    // --------------------------------------------------------------------------------------

    public function setPartial ()
    {
        $this->partialEnabled = true;

        return $this;
    }

    // --------------------------------------------------------------------------------------

    public function setMime ( $mime )
    {
        $this->mime = $mime;

        return $this;
    }

    // --------------------------------------------------------------------------------------

    public function render ()
    {
        if ( $this->filename === '' OR $this->data === '' ) {
            return false;
        }

        if ( ! ( $this->data ) ) {
            $filesize = strlen( $this->data );
        }

        if ( ! ( $this->data ) ) {
            if ( ! is_file( $this->filename ) && ( $filesize = filesize( $this->filename ) ) === false ) {
                return false;
            }

            $filepath = $this->filename;
            $filename = explode( '/', str_replace( DIRECTORY_SEPARATOR, '/', $this->filename ) );
            $filename = end( $filename );

            $filesize = filesize( $filepath );
        }

        $x = explode( '.', $filepath );
        $extension = end( $x );

        if ( count( $x ) !== 1 && isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) && preg_match(
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
                $lbyte = $filesize - 1;
            }

            $new_length = $lbyte - $fbyte;

            header( "HTTP/1.1 206 Partial Content", true );
            header( "Content-Length: $new_length", true );
            header( "Content-Range: bytes $fbyte-$lbyte/$filesize", true );
        } else {
            header( "Content-Length: " . $filesize );
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
            $file = fopen( $filepath, 'r' );
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
                $chunk_size = $this->speed > 0 ? $this->speed * 1024 : 512 * 1024;
                while ( ! feof( $file ) and ( connection_status() == 0 ) ) {
                    $buffer = fread( $file, $chunk_size );
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
                while ( $index < $filesize and ( connection_status() == 0 ) ) {
                    $left = $filesize - $index;
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