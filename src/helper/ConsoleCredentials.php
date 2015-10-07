<?php
namespace cymapgt\core\utility\console\helper;

use cymapgt\Exception\NetConsoleException;
use cymapgt\core\utility\db\DB;
use cymapgt\core\application\authentication\UserCredential\services\UserCredentialPasswordLoginService;


/**
 * Manage logging in and out of the netconsole environment
 *
 * @category    
 * @package     cymapgt.core.utility.console.helper
 * @copyright   Copyright (c) 2015 Cymap
 * @author      Cyril Ogana <cogana@gmail.com>
 */
class ConsoleCredentials
{
    //store username
    public static $username = '';
    
    //store password
    public static $password = '';
    
    //is logged in
    public static $isLoggedIn = false;
    
    /**
     * Authenticate a console user
     * 
     * Cyril Ogana <cogana@gmail.com> - 2015-08-13
     *
     * @return bool
     * 
     * @access public
     */         
    public static function authenticate() {
        $dbConn = DB::connectDb();
        
        try {
            $sqlQuery = "SELECT password FROM user WHERE username = ?";
            $dbStmt = $dbConn->prepare($sqlQuery);
            $dbStmt->bindValue(1, self::$username);
            $dbStmt->execute();
            $userArr = $dbStmt->fetch();
            
            //returned value must be array
            if (is_array($userArr)) {
                $userPassword = $userArr['password'];
            } else {
                throw new NetConsoleException('Username / Password combination is wrong');
            }
        } catch (Exception $ex) {
            throw new NetConsoleException('Could not fetch user details from DB' . $ex->getMessage());
        }
        
        $loginService = new UserCredentialPasswordLoginService();
        $loginService->setCurrentPassword($userPassword);
        $loginService->setCurrentUsername(self::$username);
        $loginService->setPassword(self::$password);
        $loginService->initialize();
        $loginResult = $loginService->authenticate();
        return $loginResult;
    }
}
