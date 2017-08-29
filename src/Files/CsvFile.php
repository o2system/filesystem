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
 * Class CsvFile
 *
 * @package O2System\Filesystem\Factory
 */
class CsvFile extends AbstractFile
{
    protected $fileExtension = '.csv';

    /**
     * CsvFile::readFile
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

        if ( is_file( $filePath ) ) {
            $handle = fopen( $filePath, 'r' );

            while ( ! feof( $handle ) ) {
                $result[] = fgetcsv( $handle );
            }
        }

        return $result;
    }

    // ------------------------------------------------------------------------

    /**
     * CsvFile::writeFile
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

        $handle = ( new File() )->create( $filePath );

        foreach ( $this->getArrayCopy() as $key => $value ) {
            if ( ! is_array( $value ) ) {
                $list = [ $key, $value ];
            } else {
                $list = $value;
            }
            fputcsv( $handle, $list );
        }

        return fclose( $handle );
    }
}