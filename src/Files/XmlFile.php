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
use O2System\Filesystem\File;

/**
 * Class XmlFile
 *
 * @package O2System\Filesystem\Factory
 */
class XmlFile extends AbstractFile
{
    /**
     * XmlFile::readFile
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

        if ( false !== ( $xml = simplexml_load_string( ( new File( $filePath ) )->read() ) ) ) {
            $result = json_decode( json_encode( $result ), true ); // force to array conversion
        }

        return $result;
    }

    // ------------------------------------------------------------------------

    /**
     * XmlFile::writeFile
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
            $root = '<' . pathinfo( $filePath, PATHINFO_FILENAME ) . '/>';

            $xml = new \SimpleXMLElement( $root );
            array_walk_recursive( $this->getArrayCopy(), [ $xml, 'addChild' ] );

            return ( new File( $filePath ) )->write( $xml->asXML() );
        }
    }
}