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

namespace O2System\Filesystem;

// ------------------------------------------------------------------------

/**
 * Class System
 *
 * @package O2System\Filesystem
 */
class System
{
    public function getInfo()
    {
        return php_uname();
    }

    // ------------------------------------------------------------------------

    public function getHostname()
    {
        return php_uname( 'n' );
    }

    // ------------------------------------------------------------------------

    public function getOS()
    {
        return php_uname( 's' );
    }

    // ------------------------------------------------------------------------

    public function getVersion()
    {
        return php_uname( 'v' );
    }

    // ------------------------------------------------------------------------

    public function getRelease()
    {
        return php_uname( 'r' );
    }

    // ------------------------------------------------------------------------

    public function getMachine()
    {
        return php_uname( 'm' );
    }

    // ------------------------------------------------------------------------

    public function getPhpSapi()
    {
        return php_sapi_name();
    }

    // ------------------------------------------------------------------------

    public function getPhpVersion()
    {
        return phpversion();
    }

    // ------------------------------------------------------------------------

    public function getPhpExtensionVersion( $extension )
    {
        return phpversion( $extension );
    }

    // ------------------------------------------------------------------------

    public function getPhpExtensions( $zendExtensions = false )
    {
        return get_loaded_extensions( $zendExtensions );
    }

    // ------------------------------------------------------------------------

    public function isPhpExtensionLoaded( $extension )
    {
        return (bool)extension_loaded( $extension );
    }

    // ------------------------------------------------------------------------

    public function getZendVersion()
    {
        return zend_version();
    }

    // ------------------------------------------------------------------------

    public function getZendOptimizerVersion()
    {
        return function_exists( 'zend_optimizer_version' ) ? zend_optimizer_version() : false;
    }

    // ------------------------------------------------------------------------

    public function getConfigurations( $extension = null, $details = true )
    {
        return call_user_func_array( 'ini_get_all', func_get_args() );
    }
}