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

use O2System\Spl\Exceptions\Logic\BadFunctionCall\BadDependencyCallException;

/**
 * Class Uploader
 *
 * @package O2System\Filesystem\Handlers
 */
class Uploader
{
    /**
     * Uploader::$path
     *
     * Uploader file destination path.
     *
     * @var string
     */
    protected $path;

    /**
     * Uploader::$maxIncrementFilename
     *
     * Maximum incremental uploaded filename.
     *
     * @var int
     */
    protected $maxIncrementFilename = 100;

    /**
     * Uploader::$allowedMimes
     *
     * Allowed uploaded file mime types.
     *
     * @var array
     */
    protected $allowedMimes;

    /**
     * Uploader::$allowedExtensions
     *
     * Allowed uploaded file extensions.
     *
     * @var array
     */
    protected $allowedExtensions;

    /**
     * Uploader::$allowedFileSize
     *
     * Allowed uploaded file size.
     *
     * @var array
     */
    protected $allowedFileSize = [
        'min' => 0,
        'max' => 0,
    ];

    /**
     * Uploader::$allowedImageSize
     *
     * Allowed uploaded image size.
     *
     * @var array
     */
    protected $allowedImageSize = [
        'width'  => [
            'min' => 0,
            'max' => 0,
        ],
        'height' => [
            'min' => 0,
            'max' => 0,
        ],
    ];

    /**
     * Uploader::$targetFilename
     *
     * Uploader target filename.
     *
     * @var string
     */
    protected $targetFilename;

    /**
     * Uploader::$errors
     *
     * Uploader error logs.
     *
     * @var array
     */
    protected $errors = [];

    // --------------------------------------------------------------------------------------

    /**
     * Uploader::__construct
     *
     * @param array $config
     *
     * @throws \O2System\Spl\Exceptions\Logic\BadFunctionCall\BadDependencyCallException
     * @throws \O2System\Spl\Exceptions\Logic\InvalidArgumentException
     */
    public function __construct( array $config = [] )
    {
        if ( ! extension_loaded( 'fileinfo' ) ) {
            throw new BadDependencyCallException( 'E_UPLOAD_FINFO_EXTENSION' );
        }

        if ( isset( $config[ 'path' ] ) ) {
            $config[ 'path' ] = str_replace( [ '\\', '/' ], DIRECTORY_SEPARATOR, $config[ 'path' ] );

            if ( is_dir( $config[ 'path' ] ) ) {
                $this->path = $config[ 'path' ];
            } elseif ( defined( 'PATH_STORAGE' ) ) {
                if ( is_dir( $config[ 'path' ] ) ) {
                    $this->path = $config[ 'path' ];
                } else {
                    $this->path = PATH_STORAGE . str_replace( PATH_STORAGE, '', $config[ 'path' ] );
                }
            } else {
                $this->path = dirname( $_SERVER[ 'SCRIPT_FILENAME' ] ) . DIRECTORY_SEPARATOR . $config[ 'path' ];
            }
        } elseif ( defined( 'PATH_STORAGE' ) ) {
            $this->path = PATH_STORAGE;
        } else {
            $this->path = dirname( $_SERVER[ 'SCRIPT_FILENAME' ] ) . DIRECTORY_SEPARATOR . 'upload';
        }

        $this->path = rtrim( $this->path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

        if ( isset( $config[ 'allowedMimes' ] ) ) {
            $this->setAllowedMimes( $config[ 'allowedMimes' ] );
        }

        if ( isset( $config[ 'allowedExtensions' ] ) ) {
            $this->setAllowedExtensions( $config[ 'allowedExtensions' ] );
        }
    }

    /**
     * Uploader::setAllowedMimes
     *
     * Set allowed mime for uploaded file.
     *
     * @param string|array $mimes List of allowed file mime types.
     *
     * @return static
     */
    public function setAllowedMimes( $mimes )
    {
        if ( is_string( $mimes ) ) {
            $mimes = explode( ',', $mimes );
        }

        $this->allowedMimes = array_map( 'trim', $mimes );

        return $this;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Uploader::setAllowedExtensions
     *
     * Set allowed extensions for uploaded file.
     *
     * @param string|array $extensions List of allowed file extensions.
     *
     * @return static
     */
    public function setAllowedExtensions( $extensions )
    {
        if ( is_string( $extensions ) ) {
            $extensions = explode( ',', $extensions );
        }

        $this->allowedExtensions = array_map( 'trim', $extensions );

        return $this;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Uploader::setPath
     *
     * Sets uploaded file path.
     *
     * @param string $path [description]
     *
     * @return static
     */
    public function setPath( $path = '' )
    {
        if ( is_dir( $path ) ) {
            $this->path = $path;
        } elseif ( defined( 'PATH_STORAGE' ) ) {
            if ( is_dir( $path ) ) {
                $this->path = $path;
            } else {
                $this->path = PATH_STORAGE . str_replace( PATH_STORAGE, '', $path );
            }
        } else {
            $this->path = dirname( $_SERVER[ 'SCRIPT_FILENAME' ] ) . DIRECTORY_SEPARATOR . $path;
        }
    }

    // --------------------------------------------------------------------------------------

    /**
     * Uploader::setMinFileSize
     *
     * Set minimum file size
     *
     * @param int    $fileSize Allowed minimum file size.
     * @param string $unit     Allowed minimum file size unit conversion.
     *
     * @return static
     */
    public function setMinFileSize( $fileSize, $unit = 'M' )
    {
        switch ( $unit ) {
            case 'B':
                $fileSize = (int)$fileSize;
                break;
            case 'K':
                $fileSize = (int)$fileSize * 1000;
                break;
            case 'M':
                $fileSize = (int)$fileSize * 1000000;
                break;
            case 'G':
                $fileSize = (int)$fileSize * 1000000000;
                break;
        }

        $this->allowedFileSize[ 'min' ] = (int)$fileSize;

        return $this;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Uploader::setMaxFileSize
     *
     * Set maximum file size
     *
     * @param int    $fileSize Allowed maximum file size.
     * @param string $unit     Allowed maximum file size unit conversion.
     *
     * @return static
     */
    public function setMaxFileSize( $fileSize, $unit = 'M' )
    {
        switch ( $unit ) {
            case 'B':
                $fileSize = (int)$fileSize;
                break;
            case 'K':
                $fileSize = (int)$fileSize * 1000;
                break;
            case 'M':
                $fileSize = (int)$fileSize * 1000000;
                break;
            case 'G':
                $fileSize = (int)$fileSize * 1000000000;
                break;
        }

        $this->allowedFileSize[ 'max' ] = (int)$fileSize;

        return $this;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Uploader::setTargetFilename
     *
     * Sets target filename.
     *
     * @param string $filename           The target filename.
     * @param string $conversionFunction Conversion function name, by default it's using dash inflector function.
     *
     * @return static
     */
    public function setTargetFilename( $filename, $conversionFunction = 'dash' )
    {
        $this->targetFilename = call_user_func_array(
            $conversionFunction,
            [
                strtolower(
                    trim(
                        $filename
                    )
                ),
            ]
        );

        return $this;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Uploader::setMaxIncrementFilename
     *
     * @param int $increment Maximum increment counter.
     *
     * @return static
     */
    public function setMaxIncrementFilename( $increment = 0 )
    {
        $this->maxIncrementFilename = (int)$increment;

        return $this;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Uploader::process
     *
     * @param string|null $field Field offset server uploaded files
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function process( $field = null )
    {
        $uploadFiles = input()->files( $field );

        if ( ! is_array( $uploadFiles ) ) {
            $uploadFiles = [ $uploadFiles ];
        }

        if ( count( $uploadFiles ) ) {
            $i = 1;
            foreach ( $uploadFiles as $file ) {
                if ( $i > $this->maxIncrementFilename ) {
                    $this->errors[] = language()->getLine(
                        'E_UPLOAD_MAXIMUM_INCREMENT_FILENAME',
                        [ $this->maxIncrementFilename ]
                    );
                }

                $targetPath = $this->path;
                $filename = $this->targetFilename;

                /* Validate extension */
                if ( is_array( $this->allowedExtensions ) ) {
                    if ( ! in_array( $file->getExtension(), $this->allowedExtensions ) ) {
                        $this->errors[] = language()->getLine(
                            'E_UPLOAD_ALLOWED_EXTENSIONS',
                            [ $this->allowedExtensions, $file->getExtension() ]
                        );
                    }
                }

                /* Validate mime */
                if ( is_array( $this->allowedMimes ) ) {
                    if ( ! in_array( $file->getFileMime(), $this->allowedMimes ) ) {
                        $this->errors[] = language()->getLine(
                            'E_UPLOAD_ALLOWED_MIMES',
                            [ $this->allowedMimes, $file->getFileMime() ]
                        );
                    }
                }

                /* Validate min size */
                if ( $this->allowedFileSize[ 'min' ] > 0 ) {
                    if ( $file->getFileSize() < $this->allowedFileSize[ 'min' ] ) {
                        $this->errors[] = language()->getLine(
                            'E_UPLOADED_ALLOWED_MIN_FILESIZE',
                            [ $this->allowedFileSize[ 'min' ], $file->getFileSize() ]
                        );
                    }
                }

                /* Validate max size */
                if ( $this->allowedFileSize[ 'min' ] > 0 ) {
                    if ( $file->getFileSize() > $this->allowedFileSize[ 'max' ] ) {
                        $this->errors[] = language()->getLine(
                            'E_UPLOADED_ALLOWED_MAX_FILESIZE',
                            [ $this->allowedFileSize[ 'max' ], $file->getFileSize() ]
                        );
                    }
                }

                if ( ! is_file( $targetPath . $filename . '-' . $i . '.' . $file->getExtension() ) ) {
                    $filename = $filename . '-' . $i . '.' . $file->getExtension();
                    $file->moveTo( $targetPath . $filename );

                    $this->errors[] = $file->getError();
                    $i = $i + 1;
                }
            }

            if ( count( $this->errors ) == 0 ) {
                return true;
            }
        }

        return false;
    }

    // --------------------------------------------------------------------------------------

    /**
     * Uploader::getErrors
     *
     * Get upload errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}