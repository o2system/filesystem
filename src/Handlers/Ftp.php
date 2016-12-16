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

use O2System\Kernel\Spl\Exceptions\RuntimeException;

/**
 * Class Ftp
 *
 * @package O2System\Filesystem\Handlers
 */
class Ftp
{
    /**
     * Ftp::$config
     *
     * Ftp configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * Passive mode flag
     *
     * @var    bool
     */
    public $isPassiveMode = true;

    /**
     * Debug flag
     *
     * Specifies whether to display error messages.
     *
     * @var    bool
     */
    public $isDebugMode = false;

    // --------------------------------------------------------------------

    /**
     * Connection ID
     *
     * @var    resource
     */
    protected $connId;

    // --------------------------------------------------------------------

    /**
     * Ftp::__construct
     */
    public function __construct ()
    {
        language()->loadFile( 'ftp' );
    }

    // --------------------------------------------------------------------

    /**
     * Ftp::connect
     *
     * Connect to FTP server.
     *
     * @param array $config Ftp configuration.
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     * @throws RuntimeException
     */
    public function connect ( array $config = [ ] )
    {
        // Prep the port
        $config[ 'port' ] = empty( $config[ 'port' ] ) ? 21 : (int) $config[ 'port' ];

        // Prep the hostname
        $config[ 'hostname' ] = preg_replace( '|.+?://|', '', $config[ 'hostname' ] );

        if ( false === ( $this->connId = @ftp_connect( $config[ 'hostname' ], $config[ 'port' ] ) ) ) {
            if ( $this->isDebugMode === true ) {
                throw new RuntimeException( 'E_FTP_UNABLE_TO_CONNECT' );
            }

            return false;
        }

        if ( false !== ( @ftp_login( $this->connId, $config[ 'username' ], $config[ 'password' ] ) ) ) {
            if ( $this->isDebugMode === true ) {
                throw new RuntimeException( 'E_FTP_UNABLE_TO_LOGIN' );
            }

            return false;
        }

        // Set passive mode if needed
        if ( $this->isPassiveMode === true ) {
            ftp_pasv( $this->connId, true );
        }

        $this->config = $config;

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Ftp::isConnected
     *
     * Validates the connection ID
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     * @throws RuntimeException
     */
    protected function isConnected ()
    {
        if ( ! is_resource( $this->connId ) ) {
            if ( $this->isDebugMode === true ) {
                throw new RuntimeException( 'E_FTP_NO_CONNECTION' );
            }

            return false;
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Ftp::changeDir
     *
     * The second parameter lets us momentarily turn off debugging so that
     * this function can be used to test for the existence of a folder
     * without throwing an error. There's no FTP equivalent to is_dir()
     * so we do it by trying to change to a particular directory.
     * Internally, this parameter is only used by the "mirror" function below.
     *
     * @param   string $remotePath    The remote directory path.
     * @param   bool   $suppressDebug Suppress debug mode.
     *
     * @return  bool  Returns TRUE on success or FALSE on failure.
     * @throws  RuntimeException
     */
    public function changeDir ( $remotePath, $suppressDebug = false )
    {
        if ( ! $this->isConnected() ) {
            return false;
        }

        $result = @ftp_chdir( $this->connId, $remotePath );

        if ( $result === false ) {
            if ( $this->isDebugMode === true AND $suppressDebug === false ) {
                throw new RuntimeException( 'E_FTP_UNABLE_TO_CHANGE_DIRECTORY' );
            }

            return false;
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Ftp::makeDir
     *
     * Create a remote directory on the ftp server.
     *
     * @param   string $remotePath  The remote directory that will be created on ftp server.
     * @param   int    $permissions The remote directory permissions.
     *
     * @return  bool Returns TRUE on success or FALSE on failure.
     * @throws  RuntimeException
     */
    public function makeDir ( $remotePath, $permissions = null )
    {
        if ( $remotePath === '' OR ! $this->isConnected() ) {
            return false;
        }

        $result = @ftp_mkdir( $this->connId, $remotePath );

        if ( $result === false ) {
            if ( $this->isDebugMode === true ) {
                throw new RuntimeException( 'E_FTP_UNABLE_TO_MAKE_DIRECTORY' );
            }

            return false;
        }

        // Set file permissions if needed
        if ( $permissions !== null ) {
            $this->setChmod( $remotePath, (int) $permissions );
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Ftp::upload
     *
     * Upload a file to the ftp server.
     *
     * @param    string $localFilePath  Local source file path.
     * @param    string $remoteFilePath Remote destination file path.
     * @param    string $mode           File transfer mode.
     * @param    int    $permissions    Remote file permissions.
     *
     * @return  bool Returns TRUE on success or FALSE on failure.
     * @throws  RuntimeException
     */
    public function upload ( $localFilePath, $remoteFilePath, $mode = 'auto', $permissions = null )
    {
        if ( ! $this->isConnected() ) {
            return false;
        }

        if ( is_file( $localFilePath ) ) {
            // Set the mode if not specified
            if ( $mode === 'auto' ) {
                // Get the file extension so we can set the upload type
                $ext = $this->getExtension( $localFilePath );
                $mode = $this->getTransferMode( $ext );
            }

            $mode = ( $mode === 'ascii' ) ? FTP_ASCII : FTP_BINARY;

            $result = @ftp_put( $this->connId, $remoteFilePath, $localFilePath, $mode );

            if ( $result === false ) {
                if ( $this->isDebugMode === true ) {
                    throw new RuntimeException( 'E_FTP_UNABLE_TO_UPLOAD' );
                }

                return false;
            }

            // Set file permissions if needed
            if ( $permissions !== null ) {
                $this->setChmod( $remoteFilePath, (int) $permissions );
            }

            return true;
        }

        return false;
    }

    // --------------------------------------------------------------------

    /**
     * Ftp::download
     *
     * Download a file from a remote server to the local server
     *
     * @param   string $remoteFilePath Remote file path.
     * @param   string $localFilePath  Local destination file path.
     * @param   string $mode           File transfer mode.
     *
     * @return  bool Returns TRUE on success or FALSE on failure.
     * @throws  RuntimeException
     */
    public function download ( $remoteFilePath, $localFilePath, $mode = 'auto' )
    {
        if ( ! $this->isConnected() ) {
            return false;
        }

        // Set the mode if not specified
        if ( $mode === 'auto' ) {
            // Get the file extension so we can set the upload type
            $ext = $this->getExtension( $remoteFilePath );
            $mode = $this->getTransferMode( $ext );
        }

        $mode = ( $mode === 'ascii' ) ? FTP_ASCII : FTP_BINARY;

        $result = @ftp_get( $this->connId, $localFilePath, $remoteFilePath, $mode );

        if ( $result === false ) {
            if ( $this->isDebugMode === true ) {
                throw new RuntimeException( 'E_FTP_UNABLE_TO_DOWNLOAD' );
            }

            return false;
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Ftp::rename
     *
     * Rename a file on ftp server.
     *
     * @param   string $oldFilename Old filename.
     * @param   string $newFilename New filename.
     *
     * @return  bool Returns TRUE on success or FALSE on failure.
     * @throws  RuntimeException
     */
    public function rename ( $oldFilename, $newFilename )
    {
        if ( ! $this->isConnected() ) {
            return false;
        }

        $result = @ftp_rename( $this->connId, $oldFilename, $newFilename );

        if ( $result === false ) {
            if ( $this->isDebugMode === true ) {
                throw new RuntimeException( 'FTP_UNABLE_TO_RENAME' );
            }

            return false;
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Ftp::moveFile
     *
     * Moves a file on the FTP server.
     *
     * @param    string $oldRemoteFilePath Old file path on the FTP server.
     * @param    string $newRemoteFilePath New file path on the FTP server.
     *
     * @return  bool Returns TRUE on success or FALSE on failure.
     * @throws  RuntimeException
     */
    public function move ( $oldRemoteFilePath, $newRemoteFilePath )
    {
        if ( ! $this->isConnected() ) {
            return false;
        }

        $result = @ftp_rename( $this->connId, $oldRemoteFilePath, $newRemoteFilePath );

        if ( $result === false ) {
            if ( $this->isDebugMode === true ) {
                throw new RuntimeException( 'FTP_UNABLE_TO_RENAME' );
            }

            return false;
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Ftp::deleteFile
     *
     * Deletes a file on the FTP server
     *
     * @param   string $filePath Path to the file to be deleted.
     *
     * @return  bool Returns TRUE on success or FALSE on failure.
     * @throws  RuntimeException
     */
    public function deleteFile ( $filePath )
    {
        if ( ! $this->isConnected() ) {
            return false;
        }

        $result = @ftp_delete( $this->connId, $filePath );

        if ( $result === false ) {
            if ( $this->isDebugMode === true ) {
                throw new RuntimeException( 'E_FTP_UNABLE_TO_DELETE' );
            }

            return false;
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Ftp::deleteDir
     *
     * Delete a folder and recursively delete everything (including sub-folders)
     * contained within it on the FTP server.
     *
     * @param   string $remotePath Path to the directory to be deleted on the FTP server.
     *
     * @return  bool Returns TRUE on success or FALSE on failure.
     * @throws  RuntimeException
     */
    public function deleteDir ( $remotePath )
    {
        if ( ! $this->isConnected() ) {
            return false;
        }

        // Add a trailing slash to the file path if needed
        $remotePath = preg_replace( '/(.+?)\/*$/', '\\1/', $remotePath );

        $list = $this->getFiles( $remotePath );
        if ( ! empty( $list ) ) {
            for ( $i = 0, $c = count( $list ); $i < $c; $i++ ) {
                // If we can't delete the item it's probaly a directory,
                // so we'll recursively call delete_dir()
                if ( ! preg_match( '#/\.\.?$#', $list[ $i ] ) && ! @ftp_delete( $this->connId, $list[ $i ] ) ) {
                    $this->deleteDir( $list[ $i ] );
                }
            }
        }

        if ( @ftp_rmdir( $this->connId, $remotePath ) === false ) {
            if ( $this->isDebugMode === true ) {
                throw new RuntimeException( 'E_FTP_UNABLE_TO_DELETE' );
            }

            return false;
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Ftp::setChmod
     *
     * Set remote file permissions.
     *
     * @param   string $remotePath Path to the remote directory or file to be changed.
     * @param   int    $mode       Remote directory permissions mode.
     *
     * @return  bool Returns TRUE on success or FALSE on failure.
     * @throws  RuntimeException
     */
    public function setChmod ( $remotePath, $mode )
    {
        if ( ! $this->isConnected() ) {
            return false;
        }

        if ( @ftp_chmod( $this->connId, $mode, $remotePath ) === false ) {
            if ( $this->isDebugMode === true ) {
                throw new RuntimeException( 'E_FTP_UNABLE_TO_CHMOD' );
            }

            return false;
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Ftp::getFiles
     *
     * FTP List files in the specified directory.
     *
     * @param    string $remotePath Path to the remote directory.
     *
     * @return  array Returns array of files list or FALSE on failure.
     * @throws  RuntimeException
     */
    public function getFiles ( $remotePath = '.' )
    {
        return $this->isConnected()
            ? ftp_nlist( $this->connId, $remotePath )
            : false;
    }

    // ------------------------------------------------------------------------

    /**
     * Ftp::mirror
     *
     * Read a directory and recreate it remotely.
     *
     * This function recursively reads a folder and everything it contains
     * (including sub-folders) and creates a mirror via FTP based on it.
     * Whatever the directory structure of the original file path will be
     * recreated on the server.
     *
     * @param    string $localPath  Path to source with trailing slash
     * @param    string $remotePath Path to destination - include the base folder with trailing slash
     *
     * @return  bool Returns TRUE on success or FALSE on failure.
     * @throws  RuntimeException
     */
    public function mirror ( $localPath, $remotePath )
    {
        if ( ! $this->isConnected() ) {
            return false;
        }

        // Open the local file path
        if ( $fp = @opendir( $localPath ) ) {
            // Attempt to open the remote file path and try to create it, if it doesn't exist
            if ( ! $this->changeDir( $remotePath, true ) && ( ! $this->makeDir( $remotePath ) OR ! $this->changeDir(
                        $remotePath
                    ) )
            ) {
                return false;
            }

            // Recursively read the local directory
            while ( false !== ( $file = readdir( $fp ) ) ) {
                if ( is_dir( $localPath . $file ) && $file[ 0 ] !== '.' ) {
                    $this->mirror( $localPath . $file . '/', $remotePath . $file . '/' );
                } elseif ( $file[ 0 ] !== '.' ) {
                    // Get the file extension so we can se the upload type
                    $ext = $this->getExtension( $file );
                    $mode = $this->getTransferMode( $ext );

                    $this->upload( $localPath . $file, $remotePath . $file, $mode );
                }
            }

            return true;
        }

        return false;
    }

    // --------------------------------------------------------------------

    /**
     * Ftp::getExtension
     *
     * Extract the file extension.
     *
     * @param   string $filename String of filename to be extracted.
     *
     * @return  string By default it's set into txt file extension.
     */
    protected function getExtension ( $filename )
    {
        return ( ( $dot = strrpos( $filename, '.' ) ) === false )
            ? 'txt'
            : substr( $filename, $dot + 1 );
    }

    // --------------------------------------------------------------------

    /**
     * Ftp::getTransferMode
     *
     * Gets upload transfer mode.
     *
     * @param   string $ext Filename extension.
     *
     * @return  string By default it's set into ascii mode.
     */
    protected function getTransferMode ( $ext )
    {
        return in_array(
            $ext,
            [ 'txt', 'text', 'php', 'phps', 'php4', 'js', 'css', 'htm', 'html', 'phtml', 'shtml', 'log', 'xml' ],
            true
        )
            ? 'ascii'
            : 'binary';
    }

    // ------------------------------------------------------------------------

    /**
     * Ftp::close
     *
     * Close the current ftp connection.
     *
     * @return  bool    Returns TRUE on success or FALSE on failure.
     * @throws  RuntimeException
     */
    public function close ()
    {
        return $this->isConnected()
            ? @ftp_close( $this->connId )
            : false;
    }
}