<?php

namespace O2System\Filesystem;

use O2System\Spl\Info\SplDirectoryInfo;

class Directory extends SplDirectoryInfo
{
    public function __construct ( $dir = null )
    {
        if ( isset( $dir ) ) {
            parent::__construct( $dir );
        }
    }

    public function make ( $dir = null, $mode = 0777, $recursive = true )
    {
        $dir = is_null( $dir ) ? $this->getPathName() : $dir;

        if ( is_dir( $dir ) ) {
            return new SplDirectoryInfo( $dir );
        } elseif ( null !== ( $pathName = $this->getPathName() ) ) {
            if ( mkdir(
                $makeDirectory = $pathName . DIRECTORY_SEPARATOR . str_replace(
                        [ '\\', '/' ],
                        DIRECTORY_SEPARATOR,
                        $dir
                    ),
                $mode,
                $recursive
            ) ) {
                return new SplDirectoryInfo( $makeDirectory );
            }
        } elseif ( mkdir(
            $makeDirectory = str_replace( [ '\\', '/' ], DIRECTORY_SEPARATOR, $dir ),
            $mode,
            $recursive
        ) ) {
            return new SplDirectoryInfo( $makeDirectory );
        }

        return false;
    }

    public function setGroup ()
    {

    }

    public function setPerms ()
    {

    }

    public function setChown ()
    {

    }

    public function setSymlink ()
    {

    }
}

