<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  net\stubbles\console
 */
namespace net\stubbles\console\ioc;
use net\stubbles\ioc\Binder;
use net\stubbles\ioc\Injector;
/**
 * Test for net\stubbles\console\ioc\ArgumentsBindingModule.
 *
 * @group  console
 * @group  console_ioc
 */
class ArgumentsBindingModuleTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  ArgumentsBindingModule
     */
    protected $argumentsBindingModule;
    /**
     * backup of $_SERVER['argv']
     *
     * @type  array
     */
    protected $argvBackup;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->argumentsBindingModule = $this->getMock('net\stubbles\console\ioc\ArgumentsBindingModule',
                                                       array('getopt')
                                        );
        $this->argvBackup             = $_SERVER['argv'];
    }

    /**
     * clean up test environment
     */
    public function tearDown()
    {
        $_SERVER['argv'] = $this->argvBackup;
    }

    /**
     * @test
     */
    public function argumentsAreBoundAsRecievedWhenNoOptionsDefined()
    {
        $_SERVER['argv'] = array('foo.php', 'bar', 'baz');
        $injector        = $this->bindArguments();
        $this->assertTrue($injector->hasConstant('argv.0'));
        $this->assertTrue($injector->hasConstant('argv.1'));
        $this->assertEquals('bar', $injector->getConstant('argv.0'));
        $this->assertEquals('baz', $injector->getConstant('argv.1'));
    }

    /**
     * @test
     * @group  issue_1
     */
    public function argumentsAreBoundAsListWhenNoOptionsDefined()
    {
        $_SERVER['argv'] = array('foo.php', 'bar', 'baz');
        $injector        = $this->bindArguments();
        $this->assertTrue($injector->hasConstant('argv'));
        $this->assertEquals(array('argv.0' => 'bar', 'argv.1' => 'baz'),
                            $injector->getConstant('argv')
        );
    }

    /**
     * @test
     */
    public function argumentsAreBoundAfterParsingWhenOptionsDefined()
    {
        $_SERVER['argv'] = array('foo.php', '-n', 'example', '--verbose', 'install');
        $this->argumentsBindingModule->expects($this->once())
                                     ->method('getopt')
                                     ->with($this->equalTo('n:f::'), $this->equalTo(array('verbose')))
                                     ->will($this->returnValue(array('n' => 'example', 'verbose' => false)));
        $this->argumentsBindingModule->withOptions('n:f::')
                                     ->withLongOptions(array('verbose'));
        $injector = $this->bindArguments();
        $this->assertTrue($injector->hasConstant('argv.n'));
        $this->assertTrue($injector->hasConstant('argv.verbose'));
        $this->assertTrue($injector->hasConstant('argv.0'));
        $this->assertEquals('example', $injector->getConstant('argv.n'));
        $this->assertFalse($injector->getConstant('argv.verbose'));
        $this->assertEquals('install', $injector->getConstant('argv.0'));
    }

    /**
     * @test
     * @group  issue_1
     */
    public function argumentsAreBoundAsListAfterParsingWhenOptionsDefined()
    {
        $_SERVER['argv'] = array('foo.php', '-n', 'example', '--verbose', 'install');
        $this->argumentsBindingModule->expects($this->once())
                                     ->method('getopt')
                                     ->with($this->equalTo('n:f::'), $this->equalTo(array('verbose')))
                                     ->will($this->returnValue(array('n' => 'example', 'verbose' => false)));
        $this->argumentsBindingModule->withOptions('n:f::')
                                     ->withLongOptions(array('verbose'));
        $injector = $this->bindArguments();
        $this->assertTrue($injector->hasConstant('argv'));
        $this->assertEquals(array('n' => 'example', 'verbose' => false, 'argv.0' => 'install'),
                            $injector->getConstant('argv')
        );
    }

    /**
     * @test
     */
    public function bindsRequestIfAvailable()
    {
        $this->assertTrue($this->bindArguments()->hasBinding('net\stubbles\input\Request'));
    }

    /**
     * @test
     */
    public function bindsConsoleRequestIfAvailable()
    {
        $this->assertTrue($this->bindArguments()->hasBinding('net\stubbles\input\console\ConsoleRequest'));
    }

    /**
     * @test
     */
    public function bindsRequestToConsoleRequest()
    {
        $this->assertInstanceOf('net\stubbles\input\console\ConsoleRequest',
                                $this->bindArguments()->getInstance('net\stubbles\input\Request')
        );
    }

    /**
     * @test
     */
    public function bindsRequestToBaseConsoleRequest()
    {
        $this->assertInstanceOf('net\stubbles\input\console\BaseConsoleRequest',
                                $this->bindArguments()->getInstance('net\stubbles\input\Request')
        );
    }

    /**
     * @test
     */
    public function bindsConsoleRequestToBaseConsoleRequest()
    {
        $this->assertInstanceOf('net\stubbles\input\console\BaseConsoleRequest',
                                $this->bindArguments()->getInstance('net\stubbles\input\console\ConsoleRequest')
        );
    }

    /**
     * @test
     */
    public function bindsRequestAsSingleton()
    {
        $injector = $this->bindArguments();
        $this->assertSame($injector->getInstance('net\stubbles\input\Request'),
                          $injector->getInstance('net\stubbles\input\Request')
        );
    }

    /**
     * @test
     */
    public function bindsConsoleRequestAsSingleton()
    {
        $injector = $this->bindArguments();
        $this->assertSame($injector->getInstance('net\stubbles\input\Request'),
                          $injector->getInstance('net\stubbles\input\console\ConsoleRequest')
        );
    }

    /**
     * binds arguments
     *
     * @return  Injector
     */
    protected function bindArguments()
    {
        $binder = new Binder();
        $this->argumentsBindingModule->configure($binder);
        return $binder->getInjector();
    }

    /**
     * binds request
     *
     * @return  net\stubbles\input\Request
     */
    private function bindRequest()
    {
        return $this->bindArguments()->getInstance('net\stubbles\input\Request');
    }

    /**
     * @test
     */
    public function argumentsAvailableViaRequestWhenNoOptionsDefined()
    {
        $_SERVER['argv'] = array('foo', 'bar', 'baz');
        $request        = $this->bindRequest();
        $this->assertTrue($request->hasParam('argv.0'));
        $this->assertTrue($request->hasParam('argv.1'));
        $this->assertEquals('bar', $request->readParam('argv.0')->unsecure());
        $this->assertEquals('baz', $request->readParam('argv.1')->unsecure());
    }

    /**
     * @test
     */
    public function argumentsAvailableViaRequestAfterParsingWhenOptionsDefined()
    {
        $_SERVER['argv'] = array('foo.php', '-n', 'example', '--verbose', 'bar');
        $this->argumentsBindingModule->expects($this->once())
                                     ->method('getopt')
                                     ->with($this->equalTo('n:f::'), $this->equalTo(array('verbose')))
                                     ->will($this->returnValue(array('n' => 'example', 'verbose' => false)));
        $this->argumentsBindingModule->withOptions('n:f::')
                                     ->withLongOptions(array('verbose'));
        $request        = $this->bindRequest();
        $this->assertTrue($request->hasParam('n'));
        $this->assertTrue($request->hasParam('verbose'));
        $this->assertTrue($request->hasParam('argv.0'));
        $this->assertEquals('example', $request->readParam('n')->unsecure());
        $this->assertFalse($request->readParam('verbose')->unsecure());
        $this->assertEquals('bar', $request->readParam('argv.0')->unsecure());
    }

    /**
     * @test
     * @expectedException  net\stubbles\lang\exception\ConfigurationException
     */
    public function invalidOptionsThrowConfigurationException()
    {
        $this->argumentsBindingModule->expects($this->once())
                                     ->method('getopt')
                                     ->with($this->equalTo('//'), $this->equalTo(array()))
                                     ->will($this->returnValue(false));
        $this->argumentsBindingModule->withOptions('//');
        $this->bindArguments();
    }

    /**
     * @test
     */
    public function optionsContainCIfStubCliEnabled()
    {
        $this->argumentsBindingModule = $this->getMock('net\stubbles\console\ioc\ArgumentsBindingModule',
                                                       array('getopt'),
                                                       array(true)
                                        );
        $this->argumentsBindingModule->expects($this->once())
                                     ->method('getopt')
                                     ->with($this->equalTo('n:f::c:'), $this->equalTo(array('verbose')))
                                     ->will($this->returnValue(array('n' => 'example', 'verbose' => false)));
        $this->argumentsBindingModule->withOptions('n:f::')
                                     ->withLongOptions(array('verbose'));
        $this->bindArguments();
    }

    /**
     * @test
     */
    public function optionsContainCIfStubCliEnabledAndOnlyLongOptionsSet()
    {
        $this->argumentsBindingModule = $this->getMock('net\stubbles\console\ioc\ArgumentsBindingModule',
                                                       array('getopt'),
                                                       array(true)
                                        );
        $this->argumentsBindingModule->expects($this->once())
                                     ->method('getopt')
                                     ->with($this->equalTo('c:'), $this->equalTo(array('verbose')))
                                     ->will($this->returnValue(array('verbose' => false)));
        $this->argumentsBindingModule->withLongOptions(array('verbose'));
        $this->bindArguments();
    }

    /**
     * @test
     */
    public function optionsContainCIfStubCliEnabledAndLongOptionsSetFirst()
    {
        $this->argumentsBindingModule = $this->getMock('net\stubbles\console\ioc\ArgumentsBindingModule',
                                                       array('getopt'),
                                                       array(true)
                                        );
        $this->argumentsBindingModule->expects($this->once())
                                     ->method('getopt')
                                     ->with($this->equalTo('n:f::c:'), $this->equalTo(array('verbose')))
                                     ->will($this->returnValue(array('n' => 'example', 'verbose' => false)));
        $this->argumentsBindingModule->withLongOptions(array('verbose'))
                                     ->withOptions('n:f::');
        $this->bindArguments();
    }

    /**
     * @test
     */
    public function bindsNoUserInputByDefault()
    {
        $injector = $this->bindArguments();
        $this->assertFalse($injector->hasConstant('net.stubbles.console.input.class'));
    }

    /**
     * @test
     */
    public function bindsUserInputIfSet()
    {
        $this->argumentsBindingModule->withUserInput('org\stubbles\console\test\BrokeredUserInput');
        $this->argumentsBindingModule->expects($this->once())
                                     ->method('getopt')
                                     ->with($this->equalTo('vo:u:h'), $this->equalTo(array('verbose', 'bar1:', 'bar2:', 'help')))
                                     ->will($this->returnValue(array()));
        $injector = $this->bindArguments();
        $this->assertTrue($injector->hasConstant('net.stubbles.console.input.class'));
        $this->assertTrue($injector->hasBinding('org\stubbles\console\test\BrokeredUserInput'));
    }

    /**
     * @since  2.1.0
     * @test
     */
    public function bindsUserInputAsSingleton()
    {
        $this->argumentsBindingModule->withUserInput('org\stubbles\console\test\BrokeredUserInput');
        $this->argumentsBindingModule->expects($this->once())
                                     ->method('getopt')
                                     ->with($this->equalTo('vo:u:h'), $this->equalTo(array('verbose', 'bar1:', 'bar2:', 'help')))
                                     ->will($this->returnValue(array('bar2' => 'foo', 'o' => 'baz')));
        $binder = new Binder();
        $this->argumentsBindingModule->configure($binder);
        $binder->bind('net\stubbles\streams\OutputStream')
               ->named('stdout')
               ->toInstance($this->getMock('net\stubbles\streams\OutputStream'));
        $binder->bind('net\stubbles\streams\OutputStream')
               ->named('stderr')
               ->toInstance($this->getMock('net\stubbles\streams\OutputStream'));
        $binder->bind('net\stubbles\streams\InputStream')
               ->named('stdin')
               ->toInstance($this->getMock('net\stubbles\streams\InputStream'));
        $binder->bindMap('net\stubbles\input\broker\param\ParamBroker')
               ->withEntry('Mock', $this->getMock('net\stubbles\input\broker\param\ParamBroker'));
        $injector = $binder->getInjector();
        $this->assertSame($injector->getInstance('org\stubbles\console\test\BrokeredUserInput'),
                          $injector->getInstance('org\stubbles\console\test\BrokeredUserInput')
        );
    }
}
?>