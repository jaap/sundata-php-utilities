<?php


namespace Sundata\Utilities\Test\Time;

use Carbon\CarbonImmutable;
use Sundata\Utilities\Time\Period;
use PHPUnit\Framework\TestCase;

class PeriodTest extends TestCase
{

    public static function periodLengthDataProvider(): array
    {
        //@formatter:off
    return [
        '12 minutes diff in seconds' =>                     ['2000-01-01T00:00:00+01:00', '2000-01-01T00:12:00+01:00', 'inSeconds', 12*60],
        '3 days, 25 min diff in seconds' =>                 ['2000-01-01T00:00:00+01:00', '2000-01-04T00:25:00+01:00', 'inSeconds', (60*60*24*3) + (25*60)],
        '1 hour diff in hours' =>                           ['2000-01-01T00:00:00+01:00', '2000-01-01T01:00:00+01:00', 'inHours',   1],
        '2 days 4 hours, 30 min diff in hours' =>           ['2000-01-01T00:00:00+01:00', '2000-01-03T04:30:00+01:00', 'inHours',   (24*2) + 4 + 0.5],
        '2 days 4 hours, 31 min, 51sec diff in hours' =>    ['2000-01-01T00:00:00+01:00', '2000-01-03T04:31:51+01:00', 'inHours',   ((24*2*60*60) + (4*60*60) + (31*60) + 51) / 3600],
        '1 day in days' =>                                  ['2000-01-01T00:00:00+01:00', '2000-01-02T00:00:00+01:00', 'inDays',    1],
        '1 day and 1 hour in days' =>                       ['2000-01-01T00:00:00+01:00', '2000-01-02T01:00:00+01:00', 'inDays',    1 + 1/24],
        '5 day and 12 hours in days' =>                     ['2000-01-01T00:00:00+01:00', '2000-01-06T12:00:00+01:00', 'inDays',    5.5],
        '1 week in weeks' =>                                ['2000-01-01T00:00:00+01:00', '2000-01-08T00:00:00+01:00', 'inWeeks',   1],
        '3.5 days in weeks' =>                              ['2000-01-01T00:00:00+01:00', '2000-01-04T12:00:00+01:00', 'inWeeks',   0.5],
        '31 days in weeks' =>                               ['2000-01-01T00:00:00+01:00', '2000-02-01T00:00:00+01:00', 'inWeeks',   31/7]
    ];
    //@formatter:on
    }

    /** @dataProvider periodLengthDataProvider */
    public function testPeriodLength($start, $end, $methodName, $expectedResult)
    {
        $start = CarbonImmutable::parse($start, 'Europe/Amsterdam');
        $end = CarbonImmutable::parse($end, 'Europe/Amsterdam');

        $period = new Period($start, $end);

        $result = $period->$methodName();
        $this->assertEquals($expectedResult, $result);
    }

    public static function isInPeriodDataProvider(): array
    {
        $defaultStart = '2000-01-01T00:00:00+01:00';
        $defaultEnd = '2000-01-02T00:00:00+01:00';
        //@formatter:off
    return [
      'Between dates' =>   [$defaultStart, $defaultEnd, '2000-01-01T00:11:00+01:00', true],
      'Before start' =>    [$defaultStart, $defaultEnd, '1999-01-01T00:11:00+01:00', false],
      'After end' =>       [$defaultStart, $defaultEnd, '2001-01-01T00:11:00+01:00', false],
      'Same as start' =>   [$defaultStart, $defaultEnd, $defaultStart, true],
      'Same as end' =>     [$defaultStart, $defaultEnd, $defaultEnd, false],
    ];
    //@formatter:on
    }

    /** @dataProvider isInPeriodDataProvider */
    public function testIsInPeriod($start, $end, $dateToTest, $expectedResult)
    {
        $start = CarbonImmutable::parse($start, 'Europe/Amsterdam');
        $end = CarbonImmutable::parse($end, 'Europe/Amsterdam');
        $dateToTest = CarbonImmutable::parse($dateToTest, 'Europe/Amsterdam');

        $period = new Period($start, $end);

        $result = $period->isInPeriod($dateToTest);
        $this->assertEquals($expectedResult, $result);
    }

    static function dstSplittingProvider(): array
    {
        return [
            'normal' => ['2018-01-01', '2019-01-01', [
                "2018-01-01T00:00:00Z",
                "2018-03-25T01:00:00Z",
                "2018-10-28T01:00:00Z",
            ]],
            'zero-size period' => ['2018-01-01', '2018-01-01', [
                "2018-01-01T00:00:00Z",
            ]],
            'period wo dst' => ['2018-01-02', '2018-01-03', [
                "2018-01-02T00:00:00Z",
            ]],
            'only dst-start' => ['2018-01-02', '2018-06-03', [
                "2018-01-02T00:00:00Z",
                "2018-03-25T01:00:00Z"
            ]],
            'only dst-end' => ['2018-06-03', '2019-02-02', [
                "2018-06-03T00:00:00Z",
                "2018-10-28T01:00:00Z"
            ]]
        ];
    }

    /** @dataProvider dstSplittingProvider */
    function testDstSplitting($start, $end, array $periodsStarts)
    {
        $periods = (new Period(
            CarbonImmutable::parse($start),
            CarbonImmutable::parse($end)
        ))->splitOnDstTransitions();

        $periodStartStrings = array_map(function (Period $period) {
            return $period->getStart()->toIso8601ZuluString();
        }, $periods);

        $this->assertSameSize($periodsStarts, $periods);
        $this->assertEmpty(array_diff($periodsStarts, $periodStartStrings));
    }

    function testDuration()
    {
        $period = new Period(
            CarbonImmutable::parse('1988-08-20'),
            CarbonImmutable::parse('2019-07-15')
        );
        $this->assertEquals(1612.2857142857142, $period->inWeeks());
        $this->assertEquals(11286, $period->inDays());
        $this->assertEquals(270864, $period->inHours());
        $this->assertEquals(16251840, $period->inMinutes());
        $this->assertEquals(975110400, $period->inSeconds());

    }

    function testToString()
    {
        $period = new Period(
            CarbonImmutable::parse('1988-08-20'),
            CarbonImmutable::parse('2019-07-15')
        );
        $expectedPeriodString = "Period[1988-08-20T00:00:00+00:00;2019-07-15T00:00:00+00:00]";
        $this->assertEquals(
            $expectedPeriodString,
            $period->toString()
        );

        $this->assertEquals(
            "some string: " . $expectedPeriodString,
            "some string: " . $period
        );
    }
}
