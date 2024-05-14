<?php

namespace Sundata\Utilities\Test\Time;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sundata\Utilities\Time\DstTransitions;

class DstTransitionsTest extends TestCase
{
    static function dataProvider(): array
    {
        return [
            [2002, '2002-03-31T01:00:00Z', '2002-10-27T01:00:00Z'],
            [2018, '2018-03-25T01:00:00Z', '2018-10-28T01:00:00Z'],
            [2021, '2021-03-28T01:00:00Z', '2021-10-31T01:00:00Z'],
            [2024, '2024-03-31T01:00:00Z', '2024-10-27T01:00:00Z'],
            // lets hope it doesn't come to this...
            [2039, '2039-03-27T01:00:00Z', '2039-10-30T01:00:00Z']
        ];
    }

    /** @dataProvider dataProvider */
    function test(int $year, $expectedStart, $expectedEnd)
    {
        $this->assertEquals($expectedStart, DstTransitions::getDstStart($year)->toIso8601ZuluString());
        $this->assertEquals($expectedEnd, DstTransitions::getDstEnd($year)->toIso8601ZuluString());
    }

    function testBefore2002IsNotAllowed(){
        $this->expectException(RuntimeException::class);
        DstTransitions::getDstStart(2001);
    }
}
