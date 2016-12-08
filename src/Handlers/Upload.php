<?php
/**
 * This file is part of the O2System PHP Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author         Steeve Andrian Salim
 *                 Mohamad Rafi Randoni
 * @copyright      Copyright (c) Steeve Andrian Salim
 */
// ------------------------------------------------------------------------

namespace O2System\Filesystem\Handlers;

use O2System\Kernel\Http\Message\ServerRequest;
use O2System\Kernel\Http\Message\UploadFile;
use O2System\Kernel\Spl\Exceptions\Logic\BadFunctionCall\BadDependencyCallException;
use O2System\Kernel\Spl\Exceptions\Logic\InvalidArgumentException;
use O2System\Kernel\Spl\Exceptions\Runtime\OverflowException;

class Upload
{

    /**
     * Upload configuration
     *
     * @var array
     */
    protected $config = [ ];

    /**
     * Upload Info
     *
     * @var array
     */
    protected $info = [ ];

    /**
     * Upload Errors
     *
     * @var array
     */
    protected $errors = [ ];

    // --------------------------------------------------------------------------------------

    public function __construct ()
    {
        if ( ! class_exists( 'finfo' ) ) {
            throw new BadDependencyCallException( 'Upload: The fileinfo extension must be loaded.', 1 );
        }

        // $default_config = config()->upload;

        if ( isset( $default_config ) ) {
            if ( isset( $default_config[ 'path' ] ) AND isset( $config[ 'path' ] ) ) {
                $config[ 'path' ] = str_replace( '/', DIRECTORY_SEPARATOR, $config[ 'path' ] );

                if ( is_dir( $config[ 'path' ] ) ) {
                    $this->config[ 'path' ] = trim( $config[ 'path' ], DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
                } else if ( is_dir( $default_config[ 'path' ] . trim( $config[ 'path' ], DIRECTORY_SEPARATOR ) ) ) {
                    $this->config[ 'path' ] = $default_config[ 'path' ] . trim(
                            $config[ 'path' ],
                            DIRECTORY_SEPARATOR
                        ) . DIRECTORY_SEPARATOR;
                } else {
                    $this->config[ 'path' ] = rtrim(
                                                  $default_config[ 'path' ],
                                                  DIRECTORY_SEPARATOR
                                              ) . DIRECTORY_SEPARATOR;
                }

                unset( $default_config[ 'path' ], $config[ 'path' ] );
            }

            $this->config = array_merge_recursive( $this->config, $config, $default_config );
        } else {
            $config = [
                'allowed_mimes' => [ ],
                'allowed_extensions' => [ 'txt', 'md' ],
                'path' => PATH_PUBLIC . '/uploads',
            ];
            $this->config = array_merge_recursive( $this->config, $config );
        }

        if ( isset( $this->config[ 'allowed_mimes' ] ) ) {
            $this->setAllowedMimes( $this->config[ 'allowed_mimes' ] );
        }

        if ( isset( $this->config[ 'allowed_extensions' ] ) ) {
            $this->setAllowedExtensions( $this->config[ 'allowed_extensions' ] );
        }
    }

    /**
     * Set allowed mime for uploaded extension
     *
     * @param string|array $mimes
     *
     * @throws InvalidArgumentException
     */
    public function setAllowedMimes ( $mimes = '' )
    {
        if ( $mimes == '' ) {
            throw new InvalidArgumentException( 'E_HEADER_INVALIDARGUMENTEXCEPTION', 1 );
        }

        if ( is_string( $mimes ) ) {
            $mimes = explode( ',', $mimes );
        }

        $this->config[ 'allowed_mimes' ] = array_map( 'trim', $mimes );
    }

    // --------------------------------------------------------------------------------------

    /**
     * Set allowed extension for uploaded file
     *
     * @param string|array $extensions
     *
     * @throws InvalidArgumentException
     */
    public function setAllowedExtensions ( $extensions )
    {
        if ( $extensions == '' ) {
            throw new InvalidArgumentException( 'E_HEADER_INVALIDARGUMENTEXCEPTION', 1 );
        }

        if ( is_string( $extensions ) ) {
            $extensions = explode( ',', $extensions );
        }

        $this->config[ 'allowed_extensions' ] = array_map( 'trim', $extensions );
    }

    // --------------------------------------------------------------------------------------

    /**
     * [setPath description]
     *
     * @param string $path [description]
     *
     * @throws InvalidArgumentException
     */
    public function setPath ( $path = '' )
    {
        if ( $path == '' ) {
            throw new InvalidArgumentException( 'E_HEADER_INVALIDARGUMENTEXCEPTION', 1 );
        }

        $this->config[ 'path' ] = $path . DIRECTORY_SEPARATOR;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Set minimal size
     *
     * @param integer $size
     * @param string  $unit
     */
    public function setMinSize ( $size, $unit = 'M' )
    {
        switch ( $unit ) {
            case 'B':
                $size = (int) $size;
                break;
            case 'K':
                $size = (int) $size * 1000;
                break;
            case 'M':
                $size = (int) $size * 1000000;
                break;
            case 'G':
                $size = (int) $size * 1000000000;
                break;
        }
        $this->config[ 'min_size' ] = (int) $size . $unit;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Set max size
     *
     * @param integer $size
     * @param string  $unit
     */
    public function setMaxSize ( $size, $unit = 'M' )
    {
        switch ( $unit ) {
            case 'B':
                $size = (int) $size;
                break;
            case 'K':
                $size = (int) $size * 1000;
                break;
            case 'M':
                $size = (int) $size * 1000000;
                break;
            case 'G':
                $size = (int) $size * 1000000000;
                break;
        }
        $this->config[ 'max_size' ] = (int) $size;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Set minimal width
     *
     * @param integer $width
     */
    public function setMinWidth ( $width = 0 )
    {
        $this->config[ 'min_width' ] = (int) $width;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Set minimal height
     *
     * @param integer $height
     */
    public function setMinHeight ( $height = 0 )
    {
        $this->config[ 'min_height' ] = (int) $height;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Set max width
     *
     * @param integer $width
     */
    public function setMaxWidth ( $width = 0 )
    {
        $this->config[ 'max_width' ] = (int) $width;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Set max height
     *
     * @param integer $height
     */
    public function setMaxHeight ( $height = 0 )
    {
        $this->config[ 'max_height' ] = (int) $height;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Set uploaded file's name
     *
     * @param string $filename
     * @param string $delimiter
     */
    public function setFilename ( $filename, $delimiter = '-' )
    {
        $filename = strtolower( trim( $filename ) );
        $filename = preg_replace( '/[^\w-]/', '', $filename );;

        if ( $delimiter === '-' ) {
            $filename = preg_replace( '/[ _]+/', '-', $filename );
        } elseif ( $delimiter === '_' ) {
            $filename = preg_replace( '/[ -]+/', '_', $filename );
        }

        $this->config[ 'filename' ] = $filename;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Set max filename's increment
     *
     * @param integer $increment
     */
    public function setMaxIncrementFilename ( $increment = 0 )
    {
        $this->config[ 'max_increment_filename' ] = (int) $increment;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Upload file
     *
     * @param string $field
     *
     * @return boolean|array
     */
    public function doUpload ( $field )
    {
        if ( $errors = $this->uploadFile( $field ) ) {
            return true;
        }

        return $errors;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Upload file to target path
     *
     * @param  string $field
     *
     * @return boolean|array
     */
    protected function uploadFile ( $field )
    {
        $serverRequest = new ServerRequest;
        $uploadFile = $serverRequest->getUploadedFiles( $field );

        $uploadFile = $uploadFile[ $field ];

        /* Make $uploadFile as an array */
        // if ( ! is_array($uploadFile)) $uploadFile[] = $uploadFile;

        $i = 1;
        $errors = [ ];
        foreach ( $uploadFile as $file ) {
            if ( $i > $this->config[ 'max_increment_filename' ] ) {
                throw new OverflowException( 'E_HEADER_OVERFLOWEXCEPTION', 1 );
            }

            $targetPath = $this->config[ 'path' ];
            $filename = $this->config[ 'filename' ];

            /* Validate extension */
            if ( isset( $this->config[ 'allowed_extensions' ] ) and count(
                                                                        $this->config[ 'allowed_extensions' ]
                                                                    ) > 0
            ) {
                $this->validateExtension( $file, $this->config[ 'allowed_extensions' ] );
            }

            /* Validate mime */
            if ( isset( $this->config[ 'allowed_mimes' ] ) and count( $this->config[ 'allowed_mimes' ] ) > 0 ) {
                $this->validateMime( $file, $this->config[ 'allowed_mimes' ] );
            }

            /* Validate min size */
            if ( isset( $this->config[ 'min_size' ] ) and $this->config[ 'min_size' ] > 0 ) {
                $this->validateMinSize( $file, $this->config[ 'min_size' ] );
            }

            /* Validate max size */
            if ( isset( $this->config[ 'max_size' ] ) and $this->config[ 'max_size' ] > 0 ) {
                $this->validateMaxSize( $file, $this->config[ 'max_size' ] );
            }

            if ( ! is_file( $targetPath . $filename . '-' . $i . '.' . $file->getExtension() ) ) {
                $filename = $filename . '-' . $i . '.' . $file->getExtension();
                $file->moveTo( $targetPath . $filename );

                $errors[] = $file->getError();
                $i = $i + 1;
            }
        }

        if ( count( $errors ) > 1 ) {
            return $errors;
        }

        return true;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Validate uploaded file extension
     *
     * @param  UploadFile $file
     * @param  array      $allowed_extensions
     *
     * @throws InvalidArgumentException
     * @return null
     */
    protected function validateExtension ( UploadFile $file, $allowed_extensions = [ ] )
    {
        if ( ! in_array( $file->getExtension(), $allowed_extensions ) ) {
            throw new InvalidArgumentException( 'E_HEADER_INVALIDARGUMENTEXCEPTION', 1 );
        }
    }

    // --------------------------------------------------------------------------------------

    /**
     * Validate uploaded file mime
     *
     * @param  UploadFile $file
     * @param  array      $allowed_mimes
     *
     * @return null
     */
    protected function validateMime ( UploadFile $file, $allowed_mimes = [ ] )
    {
        $mime = $file->getFileMime();
        if ( ! in_array( $mime, $allowed_mimes ) ) {
            throw new InvalidArgumentException( 'E_HEADER_INVALIDARGUMENTEXCEPTION', 1 );
        }
    }

    // --------------------------------------------------------------------------------------

    /**
     * Validate uploaded minimal size
     *
     * @param  UploadFile $file
     * @param  integer    $minSize
     *
     * @throws OverflowException
     * @return null
     */
    protected function validateMinSize ( UploadFile $file, $minSize = 0 )
    {
        if ( $file->getSize() < $minSize ) {
            throw new OverflowException( 'E_HEADER_OVERFLOWEXCEPTION', 1 );
        }
    }

    // --------------------------------------------------------------------------------------

    /**
     * Validate uploaded file max size
     *
     * @param  UploadFile $file
     * @param  integer    $maxSize
     *
     * @throws OverflowException
     * @return null
     */
    protected function validateMaxSize ( UploadFile $file, $maxSize = 0 )
    {
        if ( $file->getSize() > $maxSize ) {
            throw new OverflowException( 'E_HEADER_OVERFLOWEXCEPTION', 1 );
        }
    }

    // --------------------------------------------------------------------------------------

    /**
     * Get upload info
     *
     * @return array
     */
    public function getInfo ()
    {
        return $this->info;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Get upload errors
     *
     * @return array
     */
    public function getErrors ()
    {
        return $this->errors;
    }

    // --------------------------------------------------------------------------------------

}