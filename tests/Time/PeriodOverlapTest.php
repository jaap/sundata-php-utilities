<?php


namespace Sundata\Utilities\Test\Time;

use Carbon\CarbonImmutable;
use Sundata\Utilities\Time\Period;
use PHPUnit\Framework\TestCase;

class PeriodOverlapTest extends TestCase
{

    public static function periodOverlapDataProvider(): \Generator
    {
        yield 'No overlap returns null' => [
            new Period(
                CarbonImmutable::create(2000, 1, 15),
                CarbonImmutable::create(2000, 10, 15)
            ),
            new Period(
                CarbonImmutable::create(2001, 1, 15),
                CarbonImmutable::create(2001, 10, 15)
            ),
            null
        ];

        $periodA = new Period(
            CarbonImmutable::create(2000, 1, 15),
            CarbonImmutable::create(2000, 10, 15)
        );
        yield 'Getting overlap with same period returns the same period' => [
            $periodA,
            $periodA,
            $periodA
        ];

        $periodA = new Period(
            CarbonImmutable::create(2000, 1, 15),
            CarbonImmutable::create(2000, 10, 15)
        );
        yield 'Full overlap returns the same period' => [
            $periodA,
            new Period(
                CarbonImmutable::create(1900, 1, 15),
                CarbonImmutable::create(2100, 10, 15)
            ),
            $periodA
        ];

        $periodA = new Period(
            CarbonImmutable::create(2000, 1, 15),
            CarbonImmutable::create(2000, 10, 15)
        );
        $periodB = new Period(
            CarbonImmutable::create(1999, 1, 15),
            CarbonImmutable::create(2000, 5, 15)
        );
        $expectedOutcome = new Period(
            $periodA->getStart(),
            $periodB->getEnd()
        );
        yield 'Partial overlapping the start and ending somewhere in the period' => [
            $periodA,
            $periodB,
            $expectedOutcome
        ];

        $periodA = new Period(
            CarbonImmutable::create(2000, 1, 15),
            CarbonImmutable::create(2000, 10, 15)
        );

        $periodB = new Period(
            CarbonImmutable::create(2000, 5, 15),
            CarbonImmutable::create(2100, 1, 1)
        );

        $expectedOutcome = new Period(
            $periodB->getStart(),
            $periodA->getEnd()
        );
        yield 'Starting somewhere in the period and overlapping the end' => [
            $periodA,
            $periodB,
            $expectedOutcome
        ];

        yield 'There is no overlap (period A is outside of period B)' => [
            new Period(
                CarbonImmutable::create(2000, 1, 15),
                CarbonImmutable::create(2000, 10, 15)
            ),
            new Period(
                CarbonImmutable::create(1900, 1, 15),
                CarbonImmutable::create(1910, 1, 1)
            ),
            null
        ];
    }

    /**
     * @dataProvider periodOverlapDataProvider
     */
    public function testPeriodOverlap(Period $periodA, Period $periodB, $expected)
    {
        $this->assertEquals($expected, $periodA->getOverlap($periodB));
        $this->assertEquals($expected, $periodB->getOverlap($periodA));
        $this->assertSame(!($expected == null), $periodB->hasOverlap($periodA));
    }
}