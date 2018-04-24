<?php

namespace cymapgt\core\utility\console;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-08-12 at 17:56:45.
 */
class NetConsoleTest extends \PHPUnit\Framework\TestCase {

    /**
     * @var NetConsole
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }

    /**
     * @covers cymapgt\core\utility\console\NetConsole::Login
     */
    public function testLogin() {
        $userName = 'cymapgt';
        $passwordReal = '%testUserLogin';
        $passwordRealHashed = \password_hash($passwordReal, \PASSWORD_DEFAULT);

        $inputPasswordFalse = '!testUserLogin';
        $this->assertEquals(false, NetConsole::Login($userName, $inputPasswordFalse, $passwordRealHashed));

        $inputPasswordTrue = '%testUserLogin';
        $this->assertEquals(true, NetConsole::Login($userName, $inputPasswordTrue, $passwordRealHashed));
    }
}
