<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\console
 */
namespace stubbles\console\creator;
use stubbles\input\Param;
/**
 * Test for stubbles\console\creator\ClassNameFilter.
 *
 * @group  scripts
 * @since  3.0.0
 */
class ClassNameFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  ClassNameFilter
     */
    private $classNameFilter;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->classNameFilter = new ClassNameFilter();
    }

    /**
     * @return  array
     */
    public function emptyParamValues()
    {
        return [[null], ['']];
    }

    /**
     * @param  string  $value
     * @test
     * @dataProvider  emptyParamValues
     */
    public function returnsNullWhenParamValueIsEmpty($value)
    {
        $this->assertNull(
                $this->classNameFilter->apply(new Param('stdin', $value))
        );
    }

    /**
     * @param  string  $value
     * @test
     * @dataProvider  emptyParamValues
     */
    public function addsErrorToParamWhenParamValueIsEmpty($value)
    {
        $param = new Param('stdin', $value);
        $this->classNameFilter->apply($param);
        $this->assertTrue($param->hasError('CLASSNAME_EMPTY'));
    }

    /**
     * @return  array
     */
    public function invalidParamValues()
    {
        return [['500'], ['foo\500']];
    }

    /**
     * @param  string  $value
     * @test
     * @dataProvider  invalidParamValues
     */
    public function returnsNullWhenParamValueHasInvalidSyntax($value)
    {
        $this->assertNull(
                $this->classNameFilter->apply(new Param('stdin', $value))
        );
    }

    /**
     * @param  string  $value
     * @test
     * @dataProvider  invalidParamValues
     */
    public function addsErrorToParamWhenParamValueHasInvalidSyntax($value)
    {
        $param = new Param('stdin', $value);
        $this->classNameFilter->apply($param);
        $this->assertTrue($param->hasError('CLASSNAME_INVALID'));
    }

    /**
     * @test
     */
    public function trimsInputValue()
    {
        $this->assertEquals(
                'foo\bar\Baz',
                $this->classNameFilter->apply(new Param('stdin', '  foo\bar\Baz  '))
        );
    }

    /**
     * @test
     */
    public function fixesQuotedNamespaceSeparator()
    {
        $this->assertEquals(
                'foo\bar\Baz',
                $this->classNameFilter->apply(new Param('stdin', 'foo\\\\bar\\\\Baz'))
        );
    }
}
