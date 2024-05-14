<?php

namespace Sundata\Utilities\Test\String;

use InvalidArgumentException;
use Sundata\Utilities\String\StringUtil;
use PHPUnit\Framework\TestCase;

class StringUtilTest extends TestCase
{
    public static function rangesExplodeDataProvider()
    {
        return [ // @formatter:off
            // input ?invalidArg, outcome
            ["1,2,3"            , false , [1, 2, 3]],
            ["1-4"              , false , [1, 2, 3, 4]],
            ["1,3-5"            , false , [1, 3, 4, 5]],
            // ignoring spaces;
            ["0-2 , 4-5"        , false , [0, 1, 2, 4, 5]],

            ["0-, 4-5"          , true  , [0, 1, 2, 4, 5]],
            ["1.0,3.0"          , true  , [0, 1, 2, 4, 5]],
        ];// @formatter:on
    }

    /**
     * @dataProvider rangesExplodeDataProvider
     *
     * @param $ranges
     * @param $expectsInvalidArg
     * @param int[] $outcome
     */
    public function testRangeExplode(string $ranges, $expectsInvalidArg, $expected)
    {
        if ($expectsInvalidArg) {
            $this->expectException(InvalidArgumentException::class);
        }
        $actual = StringUtil::explodeRangesToArrayOfIntegers($ranges);
        $this->assertEquals(0, count(array_diff($expected, $actual)));
        $this->assertEquals(count($expected), count($actual));
    }
}