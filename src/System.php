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
    /**
     * System::getInfo
     *
     * Get Info System
     *
     * @link http://php.net/manual/en/function.php-uname.php
     * 
     * @return string Returns the description, as a string.
     */
    public function getInfo()
    {
        return php_uname();
    }

    // ------------------------------------------------------------------------

    /**
     * System::getHostname
     *
     * Get Hostname
     * 
     * @return string Host name. eg. localhost.example.com.
     */
    public function getHostname()
    {
        return php_uname( 'n' );
    }

    // ------------------------------------------------------------------------

    /**
     * System::getOS
     *
     * Get Operating System
     * 
     * @return string Operating system name. eg. FreeBSD.
     */
    public function getOS()
    {
        return php_uname( 's' );
    }

    // ------------------------------------------------------------------------

    /**
     * System::getVersion
     *
     * Get Version Of Operating System
     * 
     * @return string Version information. Varies a lot between operating systems.
     */
    public function getVersion()
    {
        return php_uname( 'v' );
    }

    // ------------------------------------------------------------------------

    /**
     * System::getRelease
     *
     * Get Release Name of Operating System
     * 
     * @return string Release name. eg. 5.1.2-RELEASE.
     */
    public function getRelease()
    {
        return php_uname( 'r' );
    }

    // ------------------------------------------------------------------------

    /**
     * System::getMachine
     *
     * Get Matchine
     * 
     * @return string Machine type. eg. i386.
     */
    public function getMachine()
    {
        return php_uname( 'm' );
    }

    // ------------------------------------------------------------------------

    /**
     * System::getPHPSapi
     *
     * Returns a lowercase string that describes the type of interface (the Server API, SAPI) that PHP is using. For example, in CLI PHP this string will be "cli" whereas with Apache it may have several different values depending on the exact SAPI used. Possible values are listed below.
     * 
     * @link http://php.net/manual/en/function.php-sapi-name.php
     * 
     * @return string Returns the interface type, as a lowercase string.
     */
    public function getPhpSapi()
    {
        return php_sapi_name();
    }

    // ------------------------------------------------------------------------

    /**
     * System::getPhpVersion
     * 
     * Gets the current PHP version
     * 
     * @return void If the optional extension parameter is specified, phpversion() returns the version of that extension, or FALSE if there is no version information associated or the extension isn't enabled.
     */
    public function getPhpVersion()
    {
        return phpversion();
    }

    // ------------------------------------------------------------------------

    /**
     * System::getPhpExtensionVersion
     * 
     * @param  string $extension An optional extension name.
     * @return void
     */
    public function getPhpExtensionVersion( $extension )
    {
        return phpversion( $extension );
    }

    // ------------------------------------------------------------------------

    /**
     * System::getPhpExtensions
     *
     * Get Php Extensions
     * 
     * @param  boolean $zendExtensions
     * @return array Returns an array with the names of all modules compiled and loaded
     */
    public function getPhpExtensions( $zendExtensions = false )
    {
        return get_loaded_extensions( $zendExtensions );
    }

    // ------------------------------------------------------------------------

    /**
     * System::isPhpExtensionLoaded
     *
     * Get Status Is Php Extension Loaded.
     * 
     * @param  string $extension An optional extension name.
     * @return boolean
     */
    public function isPhpExtensionLoaded( $extension )
    {
        return (bool)extension_loaded( $extension );
    }

    // ------------------------------------------------------------------------

    /**
     * System::getZendVersion
     * 
     * Gets the version of the current Zend engine
     * 
     * @return string Returns the Zend Engine version number, as a string.
     */
    public function getZendVersion()
    {
        return zend_version();
    }

    // ------------------------------------------------------------------------

    /**
     * System::getZendOptimizerVersion
     *
     * Get Version of Zend Optimizer 
     * 
     * @return boolean Returns TRUE if function_name exists and is a function, FALSE otherwise.
     */
    public function getZendOptimizerVersion()
    {
        return function_exists( 'zend_optimizer_version' ) ? zend_optimizer_version() : false;
    }

    // ------------------------------------------------------------------------

    /**
     * System::getConfigurations
     * 
     * @param  null|string  $extension An Optional extension name
     * @param  boolean      $details
     * @return mixed Returns the return value of the callback, or FALSE on error.
     */
    public function getConfigurations( $extension = null, $details = true )
    {
        return call_user_func_array( 'ini_get_all', func_get_args() );
    }
}