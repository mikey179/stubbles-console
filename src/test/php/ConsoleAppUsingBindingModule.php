<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles/console
 */
namespace org\stubbles\console\test;
use stubbles\console\ConsoleApp;
/**
 * Helper class to test binding module creations.
 *
 * @since  2.0.0
 */
class ConsoleAppUsingBindingModule extends ConsoleApp
{

    /**
     * creates mode binding module
     *
     * @return  ArgumentsBindingModule
     */
    public static function getArgumentsBindingModule()
    {
        return self::createArgumentsBindingModule();
    }

    /**
     * creates properties binding module
     *
     * @return  ConsoleBindingModule
     */
    public static function getConsoleBindingModule()
    {
        return self::createConsoleBindingModule();
    }

    /**
     * runs the command
     */
    public function run() { }
}
