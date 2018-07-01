<?php
namespace cymapgt\core\utility\console;

use cymapgt\Exception\NetConsoleException;
use Hoa\Console\Readline\Readlhine;
use cymapgt\core\utility\console\config\ConsoleConfig;
use cymapgt\core\utility\console\helper\ConsoleRegister;
use cymapgt\core\utility\validator\TypeValidator;

/**
 * Maintenance and Configuration tasks for the console system
 * 
 * @category    console
 * @package     cymapgt.core.utility.Console
 * @copyright   Copyright (c) 2015 Cymap
 * @author      Cyril Ogana <cogana@gmail.com>
 */
class ConsoleManager implements abstractclass\NetConsoleInterface
{   
    //Sticky commands, which are 'reverved' and  cannot be overriden
    protected static $stickyCommands = array (
        'help' => array (
            'namespace' => __CLASS__
        ),
        'add' => array (
            'namespace' => __CLASS__            
        ),
        'remove' => array (
            'namespace' => __CLASS__            
        ),
        'replace' => array (
            'namespace' =>  __CLASS__          
        ),
        'quit' =>  array (
            'namespace' => __CLASS__            
        ),
        'route' => array (
            'namespace' => __CLASS__            
        )
    );
    
    /**
     * Load help information for Classes that support the netconsole interface
     * 
     * @param string $serviceName - service name which we want to load help information
     * @param type $helpVerbosity - verbosity level of help. Should be an integer value
     * @param type $methodName - method name which is in the service we are loading help for
     * 
     * @return array
     */
    public static function help($serviceName, $helpVerbosity = 1, $methodName = null) {
        //1 - check if the service name is sticky
        $stickyServices = self::$stickyCommands;
        
        if (
            ! (is_array($stickyServices))        
        ) {
            throw new ConsoleException('The sticky services list must be an array');
        }
        
        //bool value for stickyness
        if (isset($stickyServices[($serviceName)])) {
            $isServiceSticky = true;
            $serviceNamespace = "\\" . $stickyServices[($serviceName)]['namespace'];

            //2 - load namespace details based on stickyness of service
            //first load method. swap with servicename here as console is silently the real service name
            $methodUsage = $serviceName;

            //get the method usage
            $methodUsageArr = $serviceNamespace::getUsage($serviceName, $methodUsage);            
        } else {
            $isServiceSticky = false;
            $consoleRegister = config\ConsoleConfig::$consoleRegister;
            
            if (!(is_array($consoleRegister))) {
                throw new ConsoleException('The console register must be an array');
            }
            
            if (!(isset($consoleRegister[($serviceName)]))) {
                throw new NetConsoleException("The servicename $serviceName is not registered in netconsole. Cant load help");
            }
            
            $serviceNamespace = $consoleRegister[($serviceName)];
            $methodUsageArr = $serviceNamespace::getUsage($serviceName, $methodName);               
        }
        
        //if service namespace doesn't exist, throw exception
        if (!(\class_exists($serviceNamespace))) {
            throw new NetConsoleException("The namespace for $serviceName does not exist. Cant load help");
        }
        
        return $methodUsageArr;
    }
    
    /**
     * Add a service to the API config file
     */
    public static function add($serviceName, $serviceNamespace) {
        //1 - check if the service name is sticky
        $stickyServices = self::$stickyCommands;
        
        if (
            ! (is_array($stickyServices))
        ) {
            throw new ConsoleException('The sticky services list must be an array');
        }
        
        if (
            isset($stickyServices[($serviceName)])
            || strpos(__CLASS__, $serviceNamespace) !== false
        ) {
            throw new NetConsoleException('You cannot register a sticky service name or the namespace cymapgt\core\utility\console\ConsoleManager');
        }
        
        try {
            $serviceRegistrationArray = helper\ConsoleRegister::register(array(1 => $serviceName, 2 => $serviceNamespace));
            //handle the result
            switch ($serviceRegistrationArray['status']) {
                //if service name is not registered, test and register the service (confirm first)
                case \CONSOLE_REGISTER_SERVICE_NOTFOUND:
                    echo "You are about to register service {$serviceRegistrationArray['servicename']} located in namespace {$serviceRegistrationArray['servicenamespace']} for the first time(Y/n): ";
                    $readLineConfig = new Readline();
                    $lineConfig = $readLineConfig->readLine();
                    echo netprompt();
                    
                    if (
                        $lineConfig == 'n'
                        || $lineConfig == 'N'
                    ) {
                        return;
                    }

                    ConsoleRegister::addService($serviceRegistrationArray['servicename'], $serviceRegistrationArray['servicenamespace']);
                    break;
                default:
                    echo "The service {$serviceRegistrationArray['servicename']} has already been registered. If you wish to change its namepsace"
                        . " definition do so using the remove command" . neteol();
            }                            
        } catch (NetConsoleException $ncException) {
            echo $ncException->getMessage() . neteol();
        }        
    }
    
    public static function remove($serviceName) {
        //1 - check if the service name is sticky
        $stickyServices = self::$stickyCommands;
        
        if (
            ! (is_array($stickyServices))
        ) {
            throw new ConsoleException('The sticky services list must be an array');
        }
        
        if (
            isset($stickyServices[($serviceName)])
        ) {
            throw new NetConsoleException('You cannot remove a sticky service name');
        }
        
        try {
            echo "You are about to remove service $serviceName from the netconsole API. Are you sure?(y/N): ";
            $readLineConfig = new Readline();
            $lineConfig = $readLineConfig->readLine();
            echo netprompt();

            if (
                ! ($lineConfig == 'y')
                && ! ($lineConfig == 'Y')
            ) {
                return;
            }

            ConsoleRegister::removeService($serviceName);
        } catch (NetConsoleException $ncException) {
            echo $ncException->getMessage() . neteol();
        }        
    }
    
    public static function replace($serviceName, $serviceNamespace) {
        //1 - check if the service name is sticky
        $stickyServices = self::$stickyCommands;
        
        if (
            ! (is_array($stickyServices))
        ) {
            throw new ConsoleException('The sticky services list must be an array');
        }
        
        if (
            isset($stickyServices[($serviceName)])
        ) {
            throw new NetConsoleException('You cannot perform replace operation on a sticky service name');
        }
        
        try {
            echo "You are about to perform replace operation on the service $serviceName from the netconsole API. Are you sure?(y/N): ";
            $readLineConfig = new Readline();
            $lineConfig = $readLineConfig->readLine();
            echo netprompt();

            if (
                ! ($lineConfig == 'y')
                && ! ($lineConfig == 'Y')
            ) {
                return;
            }

            $serviceRegistrationArray = helper\ConsoleRegister::register(array(1 => $serviceName, 2 => $serviceNamespace));
            //handle the result
            switch ($serviceRegistrationArray['status']) {
                //if service name is not registered, test and register the service (confirm first)
                case \CONSOLE_REGISTER_SERVICE_NOTFOUND:
                    ConsoleRegister::addService($serviceRegistrationArray['servicename'], $serviceRegistrationArray['servicenamespace']);
                    break;
                case \CONSOLE_REGISTER_SERVICE_UNCHANGED:
                    echo "The service {$serviceRegistrationArray['servicename']} is already registered with the same namespace  you are trying to issue it." . neteol();
                    break;
                default:
                    ConsoleRegister::replaceService($serviceName, $serviceNamespace);
            }
        } catch (NetConsoleException $ncException) {
            echo $ncException->getMessage() . neteol();
        }
    }
    
    public static function quit() {
        //not yet implemented
    }
    
    /**
     * Route a command to a class that implements the console interface. Appropriate validation of 
     * the commands and flags should be done prior to this
     * 
     * @param string $serviceName - service name which we want to load help information
     * @param string $serviceCommands - The method name, which should map to the command input by the user
     * @param array $commandFlags - The command flags, which will be mapped as parameters to the implementing class
     * 
     * @return bool (true)
     */    
    public static function route($serviceName, $serviceCommands, $commandFlags = null) {        
        //check that the service called actually exists
        if (!isset(ConsoleConfig::$consoleRegister[($serviceName)])) {
            throw new NetConsoleException('The service you have called is not registered on the netconsole API');
        }
       
        $serviceNamespace = ConsoleConfig::$consoleRegister[($serviceName)];
        $serviceRegistrationStatus = ConsoleRegister::serviceIsRegistered($serviceName, $serviceNamespace);
        
        if (!($serviceRegistrationStatus == 0)) {
            throw new NetConsoleException("The service $serviceName is registered but is in an unexpected state");
        }

        if (!(count($serviceCommands) > 1)) {
            throw new NetConsoleException("At least one service command should be provided along with the service name");
        }

        //Create the service command array and store the command in it
        $serviceCommandArr = array();
        $serviceCommandArr[($serviceCommands[1])] = $serviceCommands;
        
        //validate the required flags and their types
        $serviceFunctionListFull = $serviceNamespace::listFunction();
        
        if (!(isset($serviceFunctionListFull[($serviceCommands[1])]))) {
            throw new NetConsoleException("The command {$serviceCommands[1]} is not registered for service $serviceName");
        }
        
        //allocate the function name of the commands issued to a variable
        $serviceMethodName = $serviceCommands[1];

        //route to the implementing class
        $serviceFunctionList = $serviceFunctionListFull[($serviceMethodName)];
        $serviceCommandsExpected = array();
        $serviceSwitchesExpected = array();
        
        foreach ($serviceFunctionList['args'] as $serviceFunctionListEvalName => $serviceFunctionListEval) {
            $serviceFunctionListEvalType = $serviceFunctionListEval['type'];
            
            if (strpos($serviceFunctionListEvalType, 'NotNull')) {
                $serviceCommandsExpected[$serviceFunctionListEvalName] = $serviceFunctionListEval;
            } else {
                $serviceSwitchesExpected[$serviceFunctionListEvalName] = $serviceFunctionListEval;
            }
        }
        
        $commandCounter = 2;
        foreach ($serviceCommandsExpected as $commandName => $commandValidation) {
            if (!isset($serviceCommands[$commandCounter])) {
                throw new NetConsoleException("Expected console commands are mandatory. Please verify if command $commandName has been input");
            }
            
            $commandValidationMethod = $commandValidation['type'];
            $commandValidationParams = $commandValidation['params'];
            
            try {
                $commandValidationResult = TypeValidator::$commandValidationMethod($serviceCommands[($commandCounter)], $commandValidationParams);
            } catch (Exception $ex) {
                throw new NetConsoleException("The Type Validator returned an error. Confirm the validation type of the $commandName command");
            }
            
            $errorCount = 0;
            $commaConcat = '';
            if (is_array($commandValidationResult)) {
                $netConsoleExceptionMsg = "The command $commandName failed validation: ";
                
                foreach ($commandValidationResult as $validationResult) {
                    if (strlen($validationResult) > 1) {
                        if ($errorCount > 0) {
                            $commaConcat = ',';
                        }
                        
                        $netConsoleExceptionMsg .= $commaConcat . $validationResult;
                    }
                }
                
                throw new NetConsoleException($netConsoleExceptionMsg);
            }
            
            
            ++$commandCounter;
        }
        
        foreach ($serviceSwitchesExpected as $switchName => $switchValidation) {
            $listFunctionDoc = $serviceNamespace::listFunctionDocumentation();
            
            if (!isset($listFunctionDoc[($serviceCommands[1])])) {
                throw new NetConsoleException("The function {$serviceCommands[1]} is not listed in function documentation of your console API implementation");
            }
            
            if (!isset($listFunctionDoc[($serviceCommands[1])]['flags'][($switchName)]['flagid'])) {
                throw new NetConsoleException("The command switch $switchName is not listed in function documentation of your console API implementation");
            }
            
            $switchNameDocumentation = $listFunctionDoc[($serviceCommands[1])]['flags'][($switchName)]['flagid'];

            if (!isset($commandFlags[$switchNameDocumentation])) {
                continue;
            }
            
            $switchValidationMethod = $switchValidation['type'];
            $switchValidationParams = $switchValidation['params'];
            
            try {
                $switchValidationResult = TypeValidator::$switchValidationMethod($commandFlags[($switchNameDocumentation)], $switchValidationParams);
            } catch (Exception $ex) {
                throw new NetConsoleException("The Type Validator returned an error. Confirm the validation type of the $switchName command switch");
            }
            
            $errorCount = 0;
            $commaConcat = '';
            if (is_array($switchValidationResult)) {
                $netConsoleExceptionMsg = "The command switch $switchName failed validation: ";
                
                foreach ($switchValidationResult as $validationResult) {
                    if (strlen($validationResult) > 1) {
                        if ($errorCount > 0) {
                            $commaConcat = ',';
                        }
                        
                        $netConsoleExceptionMsg .= $commaConcat . $validationResult;
                    }
                }
                
                throw new NetConsoleException($netConsoleExceptionMsg);
            }        
        }
        
        //call the method
        $serviceNamespace::executeFunction($serviceCommands[1], $serviceCommands, $commandFlags);
        
        return true;
    }
    
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
                throw new NetConsoleException("The method name $methodName is not a member of $serviceName netconsole interface!");
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
        
        if (isset($listFunctionDocumentationArr) && is_array($listFunctionDocumentationArr)) {
            $listFunctionCommands = $listFunctionDocumentationArr[($serviceName)]['commands'];
            $listOfFunctions[($serviceName)]['commands'] = $listFunctionCommands;
            $listFunctionFlags = $listFunctionDocumentationArr[($serviceName)]['flags'];
            $listOfFunctions[($serviceName)]['flags'] = $listFunctionFlags;               
        }
            
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
            ),
            'remove' => array (
                'method' => 'remove',
                'args' => array (
                    'serviceName' => array (
                        'type' => 'varNotNull',
                        'params' => array (
                        )
                    )
                )
            ),
            'replace' => array (
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
            ),
            'route' => array (
                'method' => 'add',
                'args' => array (
                    'serviceName' => array (
                        'type' => 'varNotNull',
                        'params' => array (
                        )
                    ),
                    'serviceCommands' => array (
                        'type' => 'varNotNull',
                        'params' => array (
                        )
                    ),
                    'commandFlags' => array (
                        'type' => 'varNotNull',
                        'params' => array (
                        )
                    )                    
                )
            ),
            'quit' => array (
                'method' => 'quit',
                'args' => array(),
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
                        3 => "The list of sticky services for Console are add, remove, replace, help, quit and route"
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
            ),
            'remove' => array (
                'commands' => array (
                    'serviceName'
                ),
                'flags' => array (
                ),
                'docs' => array (
                    'serviceName' => array (
                        1 => "The name of an already registered service as registered in the console. You cannot remove sticky services (add, remove, replace, route, help)",
                        2 => "This service must have been registered prior to running this command, else an error is output.",
                        3 => "When you remove a service, the service name is not indexed and can be re-used with the same namespace or a new namespace in future."
                    )
                )
            ),
            'replace' => array (
                'commands' => array (
                    'serviceName',
                    'serviceNamespace'
                ),
                'flags' => array (
                ),
                'docs' => array (
                    'serviceName' => array (
                        1 => "The name of the current registered service. You cannot replace sticky services (add, remove, replace, route, help).",
                        2 => "Sticky services are those included in default functionality of the console application.",
                        3 => "When you replace a service, the service name remains indexed but loads the functionality of the new Namespace specified."
                    ),
                    'serviceNamespace' => array (
                        1 => 'The fully qualified namespace for the service being loaded to replace an existing namespace under the same serviceName',
                        2 => 'Services being registered to the API must implement as a contract the NetConsoleInterface. Console Manager will verify this',
                        3 => 'A fully qualified serviceNamespace name must begin with the root PHP namespace to ensure that there are no relative namespace resolutions being made'
                    )
                ),
            ),
            'route' => array (
                'commands' => array (
                    'serviceName',
                    'serviceCommands',
                    'commandFlags'
                ),
                'flags' => array (
                ),
                'docs' => array (
                    'serviceName' => array (
                        1 => "The name of the a service registered in the console, this doesn't include sticky services.",
                        2 => "The route command is not available for use on the console front end; it is used to handle calls all user defined services, hence in essense is a Router in applicaiton sense.",
                        3 => "All services other than the reserved services (add, remove, replace, help, quit) use the route command under the hood. Route command, much like a web framework router, receives calls to services as well as parameters through service commands and flags, and routes them to the services controller which returns a result."
                    ),
                    'serviceCommands' => array (
                        1 => 'Service commands to enable the router compile build a call to the called service. Check the services documentation for list of service commands.',
                    ),
                    'commandFlags' => array (
                        1 => 'Command flags to enable the router compile build a call to the called service. Check the services documentation for list of command flags.',
                    )                 
                )                
            ),
            'quit' => array (
                'commands' => array (
                ),
                'flags' => array (
                ),
                'docs' => array (
                )
            )
        );
    }
    
    /**
     * Execute a particular function, together with provided args
     *                             
     * Cyril Ogana <cogana@gmail.com> - 2015-08-13
     * 
     * @param string $functionName - Name of function (method) to execute
     * @param array $serviceCommands- Commands issued to the service
     * @param array $commandFlags - Flags issued to the service
     * 
     * @return bool
     * 
     * @access public
     * @static
     */
    public static function executeFunction($functionName, $serviceCommands = array(), $commandFlags = array()) {
        
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
