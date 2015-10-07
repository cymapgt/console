<?php
namespace cymapgt\core\utility\console\abstractclass;

/**
 * Packages registering with CYMAP GT NetConsole must implement this interface
 *
 * @category
 * @package     cymapgt.core.utility.Console
 * @copyright   Copyright (c) 2015 Cymap
 * @author      Cyril Ogana <cogana@gmail.com>
 */
interface NetConsoleInterface
{
    /**
     * List functionality supported by the API
     *                             
     * Cyril Ogana <cogana@gmail.com> - 2015-08-13
     *
     * @return array
     * 
     * @access public
     * @static
     */             
    public static function listFunction();
    
    /**
     * Enable fine grained processing of searching API functionality
     * 
     * Cyril Ogana <cogana@gmail.com> 2015-08-20
     * 
     * @param string $serviceName - The service name as registered in the API service
     * @param string $methodName - Optional methodname to trim down the query
     * 
     * @return array
     * 
     * @access public
     * @static
     */
    public static function getUsage($serviceName, $methodName = null);
    
    /**
     * Execute a particular function, together with provided args
     *                             
     * Cyril Ogana <cogana@gmail.com> - 2015-08-13
     * 
     * @param string $functionName - Name of function (method) to execute
     * @param array $serviceCommands - Commands issued to the service
     * @param array $commandFlags - Flags issued to the service
     * 
     * @return bool
     * 
     * @access public
     * @static
     */             
    public static function executeFunction($functionName, $serviceCommands = array(), $commandFlags = array());
}
