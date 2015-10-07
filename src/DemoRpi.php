<?php
namespace cymapgt\core\utility\console;

use cymapgt\Exception\NetConsoleException;

/**
 */
class DemoRpi implements abstractclass\NetConsoleInterface
{   
       /**
     * Enable fine grained processing of searching API functionality It also adds functionality
        * documentation for the methos
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
    public static function getUsage($serviceName, $methodName = null) {
        if ($methodName) {
            $serviceNameArr = array_keys(self::listFunction());

            if (
                ! (is_array($serviceNameArr))
                || ! (array_search($methodName, $serviceNameArr) !== false)
            ) {
                throw new NetConsoleException("The method name $methodName is not a member of $methodName netconsole interface!");
            }

            $listOfFunctionsArr = self::listFunction();
            $listOfFunctions = array($methodName => $listOfFunctionsArr[($methodName)]);
        } else {
            $listOfFunctions = self::listFunction();
        }

        //validation
        foreach ($listOfFunctions as $listOfMethods) {
            //list of functions args must be set and an array
            if (
                !(isset($listOfMethods['args']))
                || (!is_array($listOfMethods['args']))
            ) {
                throw new NetConsoleException('The list of functions in API definition must be an array');
            }            
        }

        //Load the documentation
        foreach ($listOfFunctions[($serviceName)]['args'] as $functionArgName => $functionArgDef) {
            $listFunctionDocumentationArr = self::listFunctionDocumentation();
            $listFunctionDocumentation = $listFunctionDocumentationArr[($serviceName)]['docs'][($functionArgName)];
            $listOfFunctions[($serviceName)]['args'][($functionArgName)]['docs'] = $listFunctionDocumentation;         
        }
        
        $listFunctionCommands = $listFunctionDocumentationArr[($serviceName)]['commands'];
        $listOfFunctions[($serviceName)]['commands'] = $listFunctionCommands;
        $listFunctionFlags = $listFunctionDocumentationArr[($serviceName)]['flags'];
        $listOfFunctions[($serviceName)]['flags'] = $listFunctionFlags;   
            
        return $listOfFunctions;
    }
    
    /**
     *  List functions supported by this console service
     * 
     * @return array
     * @static
     */
    public static function listFunction() {
        return array (
            'help' => array (
                'method' => 'help',
                'args' => array (
                    'serviceName' => array (
                        'type' => 'varNotNull',
                        'params' => array (
                        )
                    ),
                    'helpVerbosity' => array (
                        'type' => 'boolNull',
                        'default' => false,
                        'params' => array (
                        )
                    ),
                    'methodName' => array (
                        'type' => 'varNull',
                        'params' => array (
                        )
                    )
                )
            ),
            'add' => array (
                'method' => 'add',
                'args' => array (
                    'serviceName' => array (
                        'type' => 'varNotNull',
                        'params' => array (
                        )
                    ),
                    'serviceNamespace' => array (
                        'type' => 'varNotNull',
                        'params' => array (
                        )
                    )
                )
            )            
        );
    }

    /**
     *  Provide documentation for the function list
     * 
     * @return array
     * @static
     */
    public static function listFunctionDocumentation() {
        return array (
            'help' => array (
                'commands' => array (
                    'serviceName'
                ),
                'flags' => array (
                    'helpVerbosity' => array (
                        'flag' => '-v="..."',
                        'required' => false
                    ),
                    'methodName' => array (
                        'flag' => '-m="..."',
                        'required' => false
                    )
                ),
                'docs' => array (
                    'serviceName' => array (
                        1 => "The name of the service as registered in the console, or one of the sticky services.",
                        2 => "Sticky services are those included in default functionality of the console application.",
                        3 => "The list of sticky services for Console are add, remove, replace, quit and route"
                    ),
                    'helpVerbosity' => array (
                        1 => 'The verbosity level of the help documentation',
                        2 => 'This is usually an integer value from 1 to 5, but depends on the Console API of the service',
                        3 => 'Lower values mean less verbosity. The default verbosity level is 1'
                    ),
                    'methodName' => array (
                        1 => 'Specifying a method name here will filter the help to provide only docs for that specific method',
                        2 => 'The methodname must be specified as listed in the Console API of the service',
                        3 => 'By default, all the methods for a particular service are listed in the help if this flag is not specified'
                    )
                )
            ),
            'add' => array (
                'commands' => array (
                    'serviceName',
                    'serviceNamespace'
                ),
                'flags' => array (
                ),
                'docs' => array (
                    'serviceName' => array (
                        1 => "The name of the service as registered in the console, or one of the sticky services.",
                        2 => "Sticky services are those included in default functionality of the console application.",
                        3 => "The list of sticky services for Console are add, remove, replace, quit and route"
                    ),
                    'serviceNamespace' => array (
                        1 => 'The fully qualified namespace for the service being registered to the netconsole API',
                        2 => 'Services being registered to the API must implement as a contract the NetConsoleInterface. Console Manager will verify this',
                        3 => 'A fully qualified serviceNamespace name must begin with the root PHP namespace to ensure that there are no relative namespace resolutions being made'
                    )
                )
            )            
        );
    }
    
    /**
     * Execute a particular function
     * 
     * @param type $function
     * @param type $arguments
     * 
     * @return bool
     */
    public static function executeFunction($function, $arguments) {
        
    }
    
    /**
     * Default action when help is called
     * 
     * @param array $arguments - Arguments
     * 
     * @return string
     */
    public static function helpDefaultAction($arguments) {
        
    }
}
