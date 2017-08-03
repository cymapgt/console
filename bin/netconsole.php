<?php
require('../vendor/autoload.php');

use cymapgt\Exception\NetConsoleException;
use cymapgt\core\utility\console\ConsoleManager;
use cymapgt\core\utility\console\helper\ConsoleCredentials;
use Hoa\Console\Readline\Readline;
use Hoa\Console\Readline\Password;
use Hoa\Console\Cursor;
use Hoa\Console\Parser;

//instantiate the readline items
$readLine = new Readline();
$readPass = new Password();

//default level is in loginuserprompt (to load the userprompt screen)
$level = 'loginuserprompt';

//the main activity listening loop (implementing services may implement 1 of theirs in the execute function
do {
    if ($level == 'loginpassprompt') {
        $line = $readPass->readLine('');
    } elseif ($level == 'loginuserprompt') {
        Cursor::colorize('bold');
        echo "[cymapgt-netconsole] \n";
        Cursor::colorize('!bold');
        $line = null;
    } else {
        $line = $readLine->readLine('');
    }
    
    //If user is logged in, display netconsole> prompt
    if (ConsoleCredentials::$isLoggedIn) {
        echo 'netconsole> ';
    } else {
        echo '> ';
    }
    
    //update level for the next listening loop
    $level = processCommand($line, $level);
} while (false !== $line && 'quit' !== $line);

/**
 *  Basic routing of the appropriate command input by user
 * 
 * @param string $line
 * @param string $level
 */
function processCommand($line, $level) {
    switch ($level) {
        case 'loginuserprompt':
            echo netecho("\tUsername: ");
            return 'loginuserprocess';
        case 'loginuserprocess':
            loginuserprocess($line);
            echo netecho("\tPassword: ");
            return 'loginpassprompt';
        case 'loginpassprompt':
            loginpassprompt($line);
            return 'commandprompt';
        case 'commandprompt':
            commandprompt($line);
            return 'commandprompt';
    }
}

/**
 *  Process the username validation
 * 
 * @param string $line
 */
function loginuserprocess($line) {
    if (!(preg_match('/^[a-zA-Z0-9]*$/', $line))) {
        echo "\tUsername must be an alphanumeric entity\n";
        exit;
    }
    
    ConsoleCredentials::$username = $line;
}

/**
 * Process the user credentials validation
 * 
 * @param string $line
 * 
 */
function loginpassprompt($line) {
    ConsoleCredentials::$password = $line;
    
    try {
        $loginResult = ConsoleCredentials::authenticate();
    } catch (NetConsoleException $ncException) {
        echo "\tWrong username / password combination or user not set in db\n";
        exit;
    }

    //handle wrong credentials
    if ($loginResult !== true) {
        echo "\tWrong username / password combination\n";
        exit;        
    } else {
        ConsoleCredentials::$isLoggedIn = true;
        Cursor::colorize('bold');
        echo "Welcome to the CymapGT NetConsole.\n\n";
        echo "Copyright (c) 2017, CYMAP Business Solutions, Gomersol Technologies.\n\n";
        echo "Input a registered api name to access CGT package console functionality.\n";
        echo "Use the 'add' command to enroll a new package namespace to the console environment\n";
        echo "Use 'replace' command to update an existing packages details\n";
        echo "Similarly, the 'remove' command deregisters a namespace from the environment\n";        
        echo "or the 'quit' command to exit.\n";
        Cursor::colorize('!bold');
        echo neteol();
        return true;
    }
}

/**
 * Handle processing of console  commands as well as routing / dispatching
 * 
 * @param string $line
 */
function commandprompt($line) {
    $commandParser = new Parser();
    $commandParser->parse($line);
    $commandSwitches = $commandParser->getSwitches();
    $commandInputs = $commandParser->getInputs();
    
    if (!count($commandInputs)) {
        echo "Oops! You have not input any command into the console" . neteol();
        return;
    }
    
    $apiName = $commandInputs[0];

    $serviceName = 'console';
    $serviceNamespace = '\cymapgt\core\utility\console\ConsoleManager';
            
    switch ($apiName) {
        case 'add':
            if (!count($commandSwitches) == 0) {
                echo "Some command switches were found. Issuing the add command requires no switching!\n";
                echo "netconsole>";
                return;
            }
            
            if (
                !(isset($commandInputs[1])) 
                || !(isset($commandInputs[2]))
            ) {
                echo "The add command expects a service name and a service namespace!\n" . neteol();
                return;
            }
            
            $serviceName = $commandInputs[1];
            $serviceNamespace = $commandInputs[2];
            ConsoleManager::add($serviceName, $serviceNamespace);
            break;
        case 'replace':
            if (!count($commandSwitches) == 0) {
                echo "Some command switches were found. Issuing the replace command requires no switching!\n";
                echo "netconsole>";
                return;
            }
            
            if (
                !(isset($commandInputs[1])) 
                || !(isset($commandInputs[2]))
            ) {
                echo "The replace command expects a service name and a service namespace!\n" . neteol();
                return;
            }
            
            $serviceName = $commandInputs[1];
            $serviceNamespace = $commandInputs[2];
            ConsoleManager::replace($serviceName, $serviceNamespace);           
            break;
        case 'remove':
            if (!count($commandSwitches) == 0) {
                echo "Some command switches were found. Issuing the remove command requires no switching!" . neteol();
                return;
            }
            
            if (
                !(isset($commandInputs[1]))
            ) {
                echo "The remove command expects a service name" . neteol();
                return;
            }
            
            $serviceName = $commandInputs[1];
            ConsoleManager::remove($serviceName); 
            break;
        case 'help':
            //1 - command name must be set
            if (!isset($commandInputs[1])) {
                echo "The help command expects one command name (the Service name)" . neteol();
                return;
            }
            
            try {
                //2 - optional switches
                //no verbosity no methodname
                if (
                    ! isset($commandSwitches['v'])
                    && ! (isset($commandSwitches['m']))
                ) {
                    $helpVerbosity = null;
                    $methodName = null;
                    $methodUsageArr = ConsoleManager::help($commandInputs[1]);
                }

                //verbosity and method name
                if (
                    isset($commandSwitches['v'])
                    && (isset($commandSwitches['m']))
                ) {
                    $helpVerbosity = $commandSwitches['v'];
                    $methodName = $commandSwitches['m'];
                    $methodUsageArr = ConsoleManager::help($commandInputs[1], $commandSwitches['v'], $commandSwitches['m']);
                }

                //verbosity without method name
                if (
                    isset($commandSwitches['v'])
                    && ! (isset($commandSwitches['m']))
                ) {
                    $helpVerbosity = $commandSwitches['v'];
                    $methodName = null;
                    $methodUsageArr = ConsoleManager::help($commandInputs[1], $commandSwitches['v']);
                }

                //method name without verbosity
                if (
                    !isset($commandSwitches['v'])
                    &&  (isset($commandSwitches['m']))
                ) {
                    $helpVerbosity = null;
                    $methodName = null;
                    $methodUsageArr = ConsoleManager::help($commandInputs[1], 1, $commandSwitches['m']);
                }
            } catch (NetConsoleException $ncException) {
                echo $ncException->getMessage().  neteol();
                return;
            }
            
            //die(print_r($methodUsageArr));
            foreach ($methodUsageArr as $methodName => $methodUsageItem) {
                Cursor::colorize('bold underlined');  
                echo PHP_EOL.'SERVICE NAME: ' . $commandInputs[1].PHP_EOL;
                echo 'Method Name: ' . $methodName.PHP_EOL.PHP_EOL;
                Cursor::colorize('!bold !underlined');        
                Cursor::colorize('bold');
                echo "USAGE:".PHP_EOL;
                Cursor::colorize('!bold');
                echo $methodName;

                if (
                    isset($methodUsageItem['commands'])
                    && is_array($methodUsageItem['commands'])
                ) {
                    foreach ($methodUsageItem['commands'] as $methodUsageItemCommand) {
                        echo ' ' . $methodUsageItemCommand;
                    }
                }

                if (
                    isset($methodUsageItem['flags'])
                    && is_array($methodUsageItem['flags'])
                ) {
                    foreach ($methodUsageItem['flags'] as $methodUsageItemFlag) {
                        if ($methodUsageItemFlag['required'] === true) {
                            $methodUsageItemFlagPrefix = '';
                            $methodUsageItemFlagSuffix = '';
                        } else {
                            $methodUsageItemFlagPrefix = '[';
                            $methodUsageItemFlagSuffix = ']';                    
                        }
                        echo ' ' . $methodUsageItemFlagPrefix . $methodUsageItemFlag['flag'] . $methodUsageItemFlagSuffix;
                    }
                }

                Cursor::colorize('underlined');
                echo PHP_EOL . PHP_EOL . 'COMMANDS AND SWITCHES:'.PHP_EOL;
                Cursor::colorize('!underlined');                

                if (
                    isset($methodUsageItem['commands'])
                    && is_array($methodUsageItem['commands'])
                ) {
                     Cursor::colorize('underlined');
                    echo '* COMMANDS'.PHP_EOL.PHP_EOL;
                     Cursor::colorize('!underlined');
                    foreach ($methodUsageItem['commands'] as $methodUsageItemCommand) {
                        $methodUsageItemCommandDocs = $methodUsageItem['args'][($methodUsageItemCommand)]['docs'];
                        $verbosityLevel = $helpVerbosity;

                        if (
                            $verbosityLevel < 0
                            || $verbosityLevel > count($methodUsageItemCommandDocs)
                            || $helpVerbosity == null
                        ) {
                            $verbosityLevel = 1;
                        }

                        echo $methodUsageItemCommand;

                        if ($verbosityLevel > 0) {
                            $methodUsageItemCommandDocsSliced = array_slice($methodUsageItemCommandDocs, 0, $verbosityLevel);
                            echo "\t - " . implode($methodUsageItemCommandDocsSliced).PHP_EOL.PHP_EOL;
                        }
                    }
                }

                if (
                    isset($methodUsageItem['flags'])
                    && is_array($methodUsageItem['flags'])
                ) {
                     Cursor::colorize('underlined');
                    echo '* SWITCHES'.PHP_EOL.PHP_EOL;            
                     Cursor::colorize('!underlined');
                    foreach ($methodUsageItem['flags'] as $methodUsageItemFlagName => $methodUsageItemFlag) {
                        if ($methodUsageItemFlag['required'] === true) {
                            $methodUsageItemFlagPrefix = '';
                            $methodUsageItemFlagSuffix = '';
                        } else {
                            $methodUsageItemFlagPrefix = '';
                            $methodUsageItemFlagSuffix = '';                    
                        }

                        echo ' ' . $methodUsageItemFlagPrefix . str_replace('="..."',"",$methodUsageItemFlag['flag']) . $methodUsageItemFlagSuffix;

                        $methodUsageItemCommandDocs = $methodUsageItem['args'][($methodUsageItemFlagName)]['docs'];
                        $verbosityLevel = $helpVerbosity;

                        if (
                            $verbosityLevel < 0
                            || $verbosityLevel > count($methodUsageItemCommandDocs)
                            || $helpVerbosity == null
                        ) {
                            $verbosityLevel = 1;
                        }

                        if ($verbosityLevel > 0) {
                            $methodUsageItemCommandDocsSliced = array_slice($methodUsageItemCommandDocs, 0, $verbosityLevel);
                            echo "\t - " . implode($methodUsageItemCommandDocsSliced).PHP_EOL.PHP_EOL;
                        }                
                    }
                }        
            }
            echo neteol();             
            break;
        case 'quit':
            echo 'netconsole say bye bye...'.PHP_EOL;
            exit();
        default:
            //1 - service and method name must be set
            if (
                !isset($commandInputs[0])
                && !isset($commandInputs[1])
            ) {
                echo "Service calls expect at least the service name and one command to be input" . neteol();
                return;
            }
            
            //allocate the flags to variables
            $serviceName = (string) $commandInputs[0];
            
            try {
                //route the command
                ConsoleManager::route($serviceName, $commandInputs, $commandSwitches);                
            } catch (NetConsoleException $rtException) {
                echo $rtException->getMessage().  neteol();
                return;
            }
        }

    return;
}

/**
 *  Echo string but place prompt beforehand
 * 
 * @param string $printString
 */
function netecho($printString) {
    return ' ' . $printString;
}

/**
 *  Echo end of line then a netconsole prompt
 */
function neteol() {
    return PHP_EOL . 'netconsole>';
}

/**
 *  Enetconsole prompt
 */
function netprompt() {
    return 'netconsole>';
}
