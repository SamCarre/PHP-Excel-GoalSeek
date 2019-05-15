<?php

namespace samcarre\PHPExcelGoalSeek\Test;

use PHPUnit\Framework\TestCase;
use samcarre\PHPExcelGoalSeek\PHPExcelGoalSeek;
use samcarre\PHPExcelGoalSeek\Exceptions\PHPExcelGoalSeekException;

class GoalSeek extends \samcarre\PHPExcelGoalSeek\PHPExcelGoalSeek {

    function callbackTest($input) {
        $inputForCallbackTest2 = $input * 8;
        return $this->callbackTest2($inputForCallbackTest2);
    }
    function callbackTest2($input) {
        $solution = $input - 12;
        return $solution;
    }

    function callbackTest3($input) {
        $inputForCallbackTest4 = $input * 8;
        return $this->callbackTest4($inputForCallbackTest4);
    }
    function callbackTest4($input) {
        $solution = pow($input, 2);
        return $solution;
    }

}

class PHPExcelGoalSeekTest extends TestCase
{

    protected $goalseek;

    protected function setUp()
    {
        $this->goalseek = new GoalSeek();
    }

    protected function tearDown()
    {
        unset($this->goalseek);
    }

    public static function provider()
    {
        return array(
            array('callbackTest', 302, 5, 39.25),
            array('callbackTest', 301, 5, 39.125),
            array('callbackTest', 300, 5, 39),
            array('callbackTest', -1, 5, 1.375),
            );
    }

    public static function provider3()
    {
        return array(
            array('callbackTest3', -1, 5, null),
            );
    }


    /**
     * @dataProvider provider
     */
    public function testCalculated($testFunction, $goalseeked, $accuracy, $expected)
    {
        // Assert
        $this->assertEquals($this->goalseek->calculate($testFunction, $goalseeked, $accuracy), $expected);
    }

    /**
     * @dataProvider provider3
     */
    public function testCalculated3($testFunction, $goalseeked, $accuracy, $expected)
    {
        // Assert
        $this->assertEquals($this->goalseek->calculate($testFunction, $goalseeked, $accuracy), $expected);
    }


    public function testCalculatedNewLine()
    {
        // Assert
        $this->assertEquals($this->goalseek->newLine(), '<br />');
    }

    /**
     * @expectedException samcarre\PHPExcelGoalSeek\Exceptions\PHPExcelGoalSeekException
     */
    public function testException()
    {
        $this->goalseek->calculate('', 0, 0);
        $this->setExpectedException();
    }
}
