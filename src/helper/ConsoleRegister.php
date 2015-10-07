<?php
namespace cymapgt\core\utility\console\helper;

use cymapgt\Exception\NetConsoleException;

/**
 * Coordinate registration of Console services
 *
 * @category    
 * @package     cymapgt.core.utility.console.helper
 * @copyright   Copyright (c) 2015 Cymap
 * @author      Cyril Ogana <cogana@gmail.com>
 */
class ConsoleRegister
{
    //file location of the config file
    private static $configFile = '../config/ConsoleConfig.php';
    
    /**
     * Console service registration function
     * 
     * @param array $commandInputs
     */
    public static function register($commandInputs) {
        if (
            ! (isset($commandInputs[1]))
            || !(preg_match('/^[a-zA-Z0-9\\\\]*$/', $commandInputs[1]))
            || !(isset($commandInputs[2]))
            || !(preg_match('/^[a-zA-Z0-9\\\\]*$/', $commandInputs[2]))
        ) {
            echo('irregular characters in string');
        }
        
        //set variable names
        $serviceName = $commandInputs[1];
        $serviceNamespace = $commandInputs[2];
                
        //check if it is already registered
        $serviceRegistrationStatus = self::serviceIsRegistered($serviceName, $serviceNamespace);
        
        return array (
            'status' => $serviceRegistrationStatus,
            'servicename' => $serviceName,
            'servicenamespace' => $serviceNamespace
        );
    }
    
    /**
     * Check whether a service is already registered in the NetConsole file
     * 
     * @param string $serviceName - The name of the service
     * @param string $serviceNamespaceName - The namespace name of the service
     */
    public static function serviceIsRegistered($serviceName, $serviceNamespaceName) {
        //bootstrap service name variables
        $serviceNameCast = (string) $serviceName;
        
        //append a root namespace flag if not present
        if (!($serviceNamespaceName[0] == "\\")) {
            $serviceNamespaceNameHeader = "\\";
        } else {
            $serviceNamespaceNameHeader = '';
        }
        
        $serviceNamespaceCast = str_replace("\\","\\\\\\\\",($serviceNamespaceNameHeader . $serviceNamespaceName));
        
        //if the namespace doesnt exist, or doesnt implement our ConsoleInterface, thorw an exception
        if (
            !(class_exists(($serviceNamespaceNameHeader . $serviceNamespaceName)))
            ||
            !is_subclass_of(($serviceNamespaceNameHeader . $serviceNamespaceName), '\cymapgt\core\utility\console\abstractclass\NetConsoleInterface')
        ) {
            throw new NetConsoleException("The namespace you are registering (" . $serviceNamespaceName . ") does not exist or implement the NetConsoleInterface");
        }

        //read in config file. TODO: Place a lock here. Make sure to Chdir
        chdir(__DIR__);
        $configFile = file_get_contents ("../config/ConsoleConfig.php");
        
        //throw exception if config file not found
        if ($configFile === false) {
            throw new NetConsoleException('The console config file cannot be read!');
        }
        
        //search for the service name registration. If doesn't exists, terminate here
        $serviceNameRegex = "/[\s\t]*\"" . $serviceNameCast . "\"[\s\t]*=>[\s\t]*\"\\\\[a-zA-Z0-9_\x7f-\xff\\\\]*\"[\s\t]*,[\s\t]*/";
        
        if (!preg_match($serviceNameRegex, $configFile)) {
            return \CONSOLE_REGISTER_SERVICE_NOTFOUND;
        }
        
        //if we found it, check if it has changed or not
        $serviceNameAndNamespaceRegex = "/[\s\t]*\"" . $serviceNameCast. "\"[\s\t]*=>[\s\t]*\"". $serviceNamespaceCast. "\"[\s\t]*,[\s\t]*/";
        if (!preg_match($serviceNameAndNamespaceRegex, $configFile)) {
            return \CONSOLE_REGISTER_SERVICE_CHANGED;
        } else {
            return \CONSOLE_REGISTER_SERVICE_UNCHANGED;
        }
    }
    
    /**
     *  Check if a valid service name is in the specified line item provided as a parameter
     * 
     * @param string $serviceName - The name of the service being searched (needle)
     * @param string $lineItem - The line item (from config file) (haystack)
     * 
     * @return bool
     * @static
     */
    public static function serviceIsInLine($serviceName, $lineItem) {
        $serviceNameCast = (string) $serviceName;
        $serviceNameRegex = "/[\s\t]*\"" . $serviceNameCast . "\"[\s\t]*=>[\s\t]*\"\\\\[a-zA-Z0-9_\x7f-\xff\\\\]*\"[\s\t]*,[\s\t]*/";
        return preg_match($serviceNameRegex, $lineItem);
    }
    
    /**
     * Add a service to the Config File
     * 
     * @param string $serviceName - The name of the service
     * @param string $serviceNamespaceName - The namespace name of the service
     */    
    public static function addService($serviceName, $serviceNamespaceName) {
        //bootstrap service name variables
        $serviceNameCast = (string) $serviceName;
        
        //append a root namespace flag if not present
        if (!($serviceNamespaceName[0] == "\\")) {
            $serviceNamespaceNameHeader = "\\";
        } else {
            $serviceNamespaceNameHeader = '';
        }
        
        $serviceNamespaceCast = str_replace("\\","\\\\",($serviceNamespaceNameHeader . $serviceNamespaceName));
        
        //read in config file. TODO: Place a lock here. Make sure to Chdir
        chdir(__DIR__);
        $configFile = file ("../config/ConsoleConfig.php");
        
        //throw exception if config file not found
        if ($configFile === false) {
            throw new NetConsoleException('The console config file cannot be read!');
        }
       
        //get config line with array start. if not set, throw exception
        $linesArrayStart = preg_grep("/([\s\t]*public[\s\t]*static[\s\t]*\\\$consoleRegister[\s\t]*=[\s\t]*array[\s\t]*\\([\s\t]*)/", $configFile);

        //if lines array start is empty, throw exception
        if (count($linesArrayStart) == 0) {
            throw new NetConsoleException('The config file does not have config array start line set');
        }
        
        //if count is greater than 1, throw exception
        if (count($linesArrayStart) > 1) {
            throw new NetConsoleException('The config file cannot have more than one array start lines set');
        }
        
        //set lines start
        $lineStart = key($linesArrayStart);
        
        //get config line with the high water mark. if not set, throw exeption
        $linesArrayHwm = preg_grep("/([\s\t]*\/\/config_hwm[\s\t]*)/", $configFile);

        //if lines array high water mark is empty, throw exception
        if (count($linesArrayHwm) == 0) {
            throw new NetConsoleException('The config file does not have high water mark line set');
        }
        
        //if count is greater than 1, throw exception
        if (count($linesArrayHwm) > 1) {
            throw new NetConsoleException('The config file cannot have more than one high water mark lines set');
        }
        
        //set lines hwm
        $lineHwm = key($linesArrayHwm);
        
        //get config line with array finish. if not set, throw exception
        $linesArrayFinish = preg_grep("/([\s\t]*\);)/", $configFile);
        
        //if lines array high water mark is empty, throw exception
        if (count($linesArrayFinish) == 0) {
            throw new NetConsoleException('The config file does not have config array finish line set');
        }
        
        //if count is greater than 1, throw exception
        if (count($linesArrayFinish) > 1) {
            throw new NetConsoleException('The config file cannot have more than one config array finish lines set');
        }
        
        //set lines hwm
        $lineFinish = key($linesArrayFinish);        
        
        //if high water mark is between start and finish we are good
       if (!($lineHwm < $lineFinish && $lineHwm > $lineStart)) {
           throw new NetConsoleException('The High water config mark must between config array start and finish');
       }
       
       //instantiate new config string to write new configurations
       $newConfigString = '';

       //iterate and add new namespace in the required spot
       foreach ($configFile as $configKey => $configLineItem) {
           $newConfigString .= $configLineItem;
           if ($configKey == $lineHwm) {
               $newConfigString .= "       \"$serviceNameCast\" => \"$serviceNamespaceCast\"," . PHP_EOL;
           }
       }
       
       //write the config file
       if (file_put_contents('../config/ConsoleConfig.php', $newConfigString) === false) {
           throw new NetConsoleException('The config file failed to write');
       }
    }
    
    /**
     * Remove a service from the Config file
     * 
     * @param string $serviceName - The name of the service
     */
    public static function removeService($serviceName) {
        //bootstrap service name variables
        $serviceNameCast = (string) $serviceName;
               
        //read in config file. TODO: Place a lock here. Make sure to Chdir
        chdir(__DIR__);
        $configFile = file ("../config/ConsoleConfig.php");
        
        //throw exception if config file not found
        if ($configFile === false) {
            throw new NetConsoleException('The console config file cannot be read!');
        }
        
        //flag to decide if to rewrite the file or not
        $rewriteFlag = false;
        
        //iterate and search for the service details
        foreach ($configFile as $configLine) {
            if (1 === self::serviceIsInLine($serviceNameCast, $configLine)) {
                $keyLocation = key($configFile) - 1;    //decrement by 1 because foreach increments keys before, not after the iteration
                
                unset($configFile[($keyLocation)]);
                $rewriteFlag = true;
            }
        }
        
        //if regex failed, throw and handle exception
        if ($rewriteFlag === false) {
            throw new NetConsoleException("The service $serviceNameCast was not found in the config file. Cannot remove.");
        }
        
        //Rewrite the file
        $configFileString = '';
        
        //write to string
        foreach ($configFile as $configKey => $configLine) {
            if (!($keyLocation == $configKey)) {
                $configFileString .= $configLine;
            }
        }
        
       //write the config file
       if (file_put_contents('../config/ConsoleConfig.php', $configFileString) === false) {
           throw new NetConsoleException('The config file failed to write');
       }
    }
    
    /**
     * Replace a service from the Config file. This means if it exists, it will be updated. If it doesnt exist
     * it will be created
     * 
     * @param string $serviceName - The name of the service
     * @param string $serviceNamespace - The namespace of the service
     */
    public static function replaceService($serviceName, $serviceNamespace) {
        //bootstrap service name variables
        $serviceNameCast = (string) $serviceName;
               
        //append a root namespace flag if not present
        if (!($serviceNamespace[0] == "\\")) {
            $serviceNamespaceNameHeader = "\\";
        } else {
            $serviceNamespaceNameHeader = '';
        }
        
        $serviceNamespaceCast = str_replace("\\","\\\\",($serviceNamespaceNameHeader . $serviceNamespace));        
        //read in config file. TODO: Place a lock here. Make sure to Chdir
        chdir(__DIR__);
        $configFile = file ("../config/ConsoleConfig.php");
        
        //throw exception if config file not found
        if ($configFile === false) {
            throw new NetConsoleException('The console config file cannot be read!');
        }
        
        //flag to decide if to rewrite the file or not
        $rewriteFlag = false;
        
        //iterate and search for the service details
        foreach ($configFile as $configLine) {
            if (1 === self::serviceIsInLine($serviceNameCast, $configLine)) {
                $keyLocation = key($configFile) - 1;    //decrement by 1 because foreach increments keys before, not after the iteration
                $configFile[($keyLocation)] = "        \"$serviceNameCast\" => \"$serviceNamespaceCast\"," . PHP_EOL;
                $rewriteFlag = true;
            }
        }

        //if regex failed, throw and handle exception
        if ($rewriteFlag === false) {
            throw new NetConsoleException("The service $serviceNameCast was not found in the config file. Cannot remove.");
        }
        
        //Rewrite the file
        $configFileString = '';
        
        //write to string
        foreach ($configFile as $configKey => $configLine) {
            $configFileString .= $configLine;
        }
        
       //write the config file
       if (file_put_contents('../config/ConsoleConfig.php', $configFileString) === false) {
           throw new NetConsoleException('The config file failed to write');
       }
    }    
}
