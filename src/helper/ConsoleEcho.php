<?php
namespace cymapgt\core\utility\console\helper;

use cymapgt\Exception\NetConsoleException;


/**
 * Enable the same Output functionality for the prompt based on netconsole functions. For
 * use by packages that implement NetConsole API
 *
 * @category    
 * @package     cymapgt.core.utility.console.helper
 * @copyright   Copyright (c) 2015 Cymap
 * @author      Cyril Ogana <cogana@gmail.com>
 */
class ConsoleEcho
{
    /**
     * Provide netconsoles netecho function to implementing packages
     * 
     * Cyril Ogana <cogana@gmail.com> - 2015-08-13
     *
     * @param $printString - The string to print out
     * 
     * @return string
     * 
     * @access public
     */         
    public static function netEcho($printString) {
        return " " . $printString;
    }
    
    /**
     * Provide netconsoles neteol function to implementing packages
     * 
     * Cyril Ogana <cogana@gmail.com> - 2015-08-13
     *
     * @return string
     * 
     * @access public
     */         
    public static function netEol() {
        return PHP_EOL . 'netconsole>';
    }
    
    /**
     * Provide netconsoles netecho function to implementing packages
     * 
     * Cyril Ogana <cogana@gmail.com> - 2015-08-13
     *
     * @return string
     * 
     * @access public
     */         
    public static function netPrompt() {
        return 'netconsole>';
    }    
}
