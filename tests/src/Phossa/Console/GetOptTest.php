<?php
namespace Phossa\Console;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-12-15 at 16:39:36.
 */
class GetOptTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GetOpt
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new GetOpt;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * common setOpt
     *
     * @covers Phossa\Console\GetOpt::setOpt
     */
    public function testSetOpt1()
    {
        $this->object->setOpt('user');
        $this->assertTrue($this->object->get('user') instanceof Option);
    }

    /**
     * test duplicated option
     *
     * @covers Phossa\Console\GetOpt::setOpt
     * @expectedException Phossa\Console\Exception\DuplicationFoundException
     * @expectedExceptionCode Phossa\Console\Message\Message::GETOPT_OPTION_NAME_DUP
     */
    public function testSetOpt2()
    {
        $this->object->setOpt('u');
        $this->object->setOpt('u');
    }

    /**
     * option factory test
     *
     * @covers Phossa\Console\GetOpt::setOpt
     */
    public function testSetOpt3()
    {
        $this->object = new GetOpt([], function($n, $rules) {
            // reset rules
            $rules['help'] = 'Help message';
            $rules['lang'] = 'zh_CN.gbk';
            return new Option($n, $rules);
        });

        $this->object->setOpt('user');
        $opt = $this->object->get('user');
        $this->assertEquals('zh_CN.gbk', $opt->getRules()['lang']);
    }

    /**
     * test short options 1
     *
     * @covers Phossa\Console\GetOpt::getOpt
     */
    public function testGetOpt1()
    {
        $def = 'hi:o:v::';
        list($a, $b) = $this->object->getOpt(['-h', 'test'], $def);
        $this->assertArrayHasKey('h', $a);
        $this->assertEquals(['test'], $b);
    }

    /**
     * test short options, value required
     *
     * @covers Phossa\Console\GetOpt::getOpt
     */
    public function testGetOpt2()
    {
        $def = 'hi:o:v::';
        list($a,) = $this->object->getOpt(['-i', 'test'], $def);
        $this->assertEquals(['i' => 'test'], $a);
    }

    /**
     * test short options, missing value
     *
     * @covers Phossa\Console\GetOpt::getOpt
     * @expectedException Phossa\Console\Exception\InvalidArgumentException
     * @expectedExceptionCode Phossa\Console\Message\Message::GETOPT_ARGUMENT_ERROR
     */
    public function testGetOpt3()
    {
        $def = 'hi:o:v::';
        $this->object->getOpt(['-o'], $def);
    }

    /**
     * test short options, optional value
     *
     * @covers Phossa\Console\GetOpt::getOpt
     */
    public function testGetOpt4()
    {
        $def = 'hi:o:v::';

        list($a,) = $this->object->getOpt(['-v'], $def);
        $this->assertSame(['v' => ''], $a);

        list($a,) = $this->object->getOpt(['-v', 'bingo']);
        $this->assertSame(['v' => 'bingo'], $a);
    }

    /**
     * test short options, required option
     *
     * @covers Phossa\Console\GetOpt::getOpt
     * @expectedException Phossa\Console\Exception\InvalidArgumentException
     * @expectedExceptionCode Phossa\Console\Message\Message::GETOPT_OPTION_MISSING
     * @expectedExceptionMessage Option "o" missing
     */
    public function testGetOpt5()
    {
        $def = 'hi:o#:v::';
        $this->object->getOpt(['-i', 'file.php'], $def);
    }

    /**
     * test long options
     *
     * @covers Phossa\Console\GetOpt::getOpt
     */
    public function testGetOpt6()
    {
        $def = 'user|u=';
        list($a,) = $this->object->getOpt(['--user=phossa'], $def);
        $this->assertSame(['user' => 'phossa'], $a);

        list($b,) = $this->object->getOpt(['-u', 'phossa']);
        $this->assertSame(['user' => 'phossa'], $b);
    }

    /**
     * test complex options
     *
     * @covers Phossa\Console\GetOpt::getOpt
     */
    public function testGetOpt7()
    {
        $def = 'user|u=,o::,i#:[s=a|b|c],quiet|q';
        list($a, $b) = $this->object->getOpt([
            '--user=phossa',
            '-qo', 'output.txt',
            '-ib',
            '-i', 'c',
            'wow'
        ], $def);
        $this->assertSame([
            'user'  => 'phossa',
            'o'     => 'output.txt',
            'i'     => ['b', 'c'],
            'quiet' => ''
        ], $a);
        $this->assertSame(['wow'], $b);
    }

    /**
     * test value type
     *
     * @covers Phossa\Console\GetOpt::getOpt
     * @expectedException Phossa\Console\Exception\InvalidArgumentException
     * @expectedExceptionCode Phossa\Console\Message\Message::GETOPT_INVALID_TYPE
     * @expectedExceptionMessage Invalid value type for option "d"
     */
    public function testGetOpt8()
    {
        $def = 'user|u=,d#:[i],c#::[i=2|3|4],o::,q|quiet';
        $this->object->getOpt([
            '-d', 'a'
        ], $def);
    }

    /**
     * test invalid value
     *
     * @covers Phossa\Console\GetOpt::getOpt
     * @expectedException Phossa\Console\Exception\InvalidArgumentException
     * @expectedExceptionCode Phossa\Console\Message\Message::GETOPT_INVALID_VALUE
     * @expectedExceptionMessage Invalid value "5" for option "c"
     */
    public function testGetOpt9()
    {
        $def = 'user|u=,d#:[i],c#::[i=2|3|4],o::,q|quiet';
        $this->object->getOpt([
            '-d', '1', '-c', '5'
        ], $def);
    }

    /**
     * test invalid value
     *
     * @covers Phossa\Console\GetOpt::getOpt
     * @expectedException Phossa\Console\Exception\InvalidArgumentException
     * @expectedExceptionCode Phossa\Console\Message\Message::GETOPT_INVALID_VALUE
     * @expectedExceptionMessage Invalid value "5" for option "c"
     */
    public function testGetOpt10()
    {
        $def = 'user|u=,d#:[i],c#::[i=2|3|4],o::,q|quiet';
        $this->object->getOpt([
            '-d', '1', '-c', '5'
        ], $def);
    }

    /**
     * option array definition
     *
     * @covers Phossa\Console\GetOpt::getOpt
     */
    public function testGetOpt11()
    {
        $def = ['h', 'd#=[n]','c::', 'o::', 'x::[=wow|wow2]'];
        list($a,) = $this->object->getOpt(['-d','1.0', '-xwow'], $def);
        $this->assertSame([
            'd'  => '1.0',
            'x'  => 'wow',
        ], $a);
    }
}
