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

use O2System\Filesystem\Abstracts\AbstractFile;

/**
 * Class CsvFile
 *
 * @package O2System\Filesystem\Factory
 */
class CsvFile extends AbstractFile
{
    /**
     * CsvFile::readFile
     *
     * @param string $filePath Path to the file.
     * @param array  $options  Read file options.
     *
     * @return mixed
     */
    public function readFile ( $filePath, array $options = [ ] )
    {
        $filePath = empty( $filePath )
            ? $this->filePath
            : $filePath;


        $result = [ ];

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
    public function writeFile ( $filePath = null, array $options = [ ] )
    {
        $filePath = empty( $filePath )
            ? $this->filePath
            : $filePath;

        $handle = fopen( $filePath, 'wb' );

        foreach ( $this->getArrayCopy() as $list ) {
            fputcsv( $handle, $list );
        }

        return fclose( $handle );
    }
}