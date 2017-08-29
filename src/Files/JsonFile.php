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

namespace O2System\Filesystem\Files;

// ------------------------------------------------------------------------

use O2System\Filesystem\Files\Abstracts\AbstractFile;
use O2System\Filesystem\File;

/**
 * Class JsonFile
 *
 * @package O2System\Filesystem\Factory
 */
class JsonFile extends AbstractFile
{
    protected $fileExtension = '.json';

    /**
     * JsonFile::readFile
     *
     * @param string $filePath Path to the file.
     * @param array  $options  Read file options.
     *
     * @return mixed
     */
    public function readFile( $filePath, array $options = [] )
    {
        $filePath = empty( $filePath )
            ? $this->filePath
            : $filePath;

        $result = [];

        if ( false !== ( $json = json_decode( ( new File( $filePath ) )->read() ) ) ) {
            if ( json_last_error() === JSON_ERROR_NONE ) {
                $result = $json;
            }
        }

        return $result;
    }

    // ------------------------------------------------------------------------

    /**
     * JsonFile::writeFile
     *
     * @param string $filePath Path to the file.
     * @param array  $options  Write file options.
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function writeFile( $filePath = null, array $options = [] )
    {
        $filePath = empty( $filePath )
            ? $this->filePath
            : $filePath;

        if ( $this->count() ) {
            return ( new File() )->write( $filePath, json_encode( $this->getArrayCopy(), JSON_PRETTY_PRINT ) );
        }
    }
}