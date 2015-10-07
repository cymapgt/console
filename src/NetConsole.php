<?php
namespace cymapgt\core\utility\console;

use cymapgt\Exception\NetConsoleException;
use cymapgt\Exception\UserCredentialException;
use cymapgt\core\application\authentication\UserCredential\services\UserCredentialPasswordLoginService;

/**
 * Core logic for the netconsole. This provides core business logic that will be used by the netconsole
 * application
 *
 * @category    
 * @package     cymapgt.core.utility.Console
 * @copyright   Copyright (c) 2015 Cymap
 * @author      Cyril Ogana <cogana@gmail.com>
 */
class NetConsole
{
    /**
     *  Prevent direct object creation
     * 
     * @private
     * @final
     */
    final private function __construct() {
    }
    
    /**
     * Prevent cloning
     * 
     * @private
     * @final
     */
    final private function __clone() {
    }
    
    /**
     * log in authentication method
     * 
     * Cyril Ogana <cogana@gmail.com> - 2015-08-12
     * 
     * @access public
     */
    public function Login($userName, $inputPassword, $currentPassword) {
        $userCredentialService = new UserCredentialPasswordLoginService();
        $userCredentialService->setCurrentUsername($userName);
        $userCredentialService->setCurrentPassword($currentPassword);
        $userCredentialService->setPassword($inputPassword);
        
        try {
            $userCredentialService->initialize();
            $loginIsValid = $userCredentialService->authenticate();
        } catch (UserCredentialException $ucException) {
            throw new NetConsoleException('An exception occurred when logging in! ' . $ucException->getMessage(), 1001);
        }
        
        return $loginIsValid;
    }
}
