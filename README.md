# PHP Excel GoalSeek

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Latest Version](https://img.shields.io/github/release/davidjr82/PHP-Excel-GoalSeek.svg?style=flat-square)](https://github.com/samcarre/PHP-Excel-GoalSeek/releases)

Utility to emulate goalseek function for PHP 7

## Usage

``` php
//First, wrap your functions in your own class that extends PHPExcelGoalSeek
class GoalSeek extends \davidjr82\PHPExcelGoalSeek\PHPExcelGoalSeek {

    function callbackTest($input) {
        $inputForCallbackTest2 = $input * 8;
        return $this->callbackTest2($inputForCallbackTest2);
    }

    function callbackTest2($input) {
        $solution = $input - 12;
        return $solution;
    }
}

//Instantiate your class
$goalseek = new GoalSeek();
//$goalseek->debug = true;

//I want to know which input needs callbackTest to give me 301
$expected_result = 300;

//Calculate the input to get you goal, with accuracy
$input = $goalseek->calculate('callbackTest', $expected_result, 5);

//Voilá!
echo "\$input: " . $input . "<br />";

//Let's test our input it is close
$actual_result = $goalseek->callbackTest($input);
//Searched result of function
echo "Searched result of callbackTest(\$input) = " . $expected_result . "<br />";
//Actual result of function with calculated goalseek
echo "Actual result of callbackTest(" . $input . ") = " . $actual_result . "<br />";
//If difference is too high, you can improve the class and send me it your modifications ;)
echo "Difference = " . ($actual_result - $expected_result);
```

## Testing

``` bash
$ phpunit
```

## Security

If you discover any security related issues, please email sam@codepotato.co.uk instead of using the issue tracker.

## Credits

- [David Jiménez](https://github.com/davidjr82) (Original Creator)
- [Sam Carre](https://github.com/samcarre) (Maintainer)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
