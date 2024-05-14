<?php

namespace Sundata\Utilities\Test\Radiation;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Generator;
use PHPUnit\Framework\TestCase;
use Sundata\Utilities\Radiation\RadiationInPeriod;
use Sundata\Utilities\Time\Period;

class RadiationInPeriodTest extends TestCase
{
    public static function RadiationInPeriodDataProvider(): Generator
    {
        yield 'Full year, but you forget end-is-exclusive' => [
            '2019-01-01',
            '2019-12-31',
            386519,
            99.94
        ];

        yield 'Actual full year' => [
            '2019-01-01',
            '2020-01-01',
            386751,
            100.000
        ];

        yield 'Full leap year is the same value as regular year' => [
            '2020-01-01',
            '2021-01-01',
            386751,
            100.000
        ];

        yield "year starting in june" => [
            '2021-06-01',
            '2022-06-01',
            386751,
            100.000
        ];

        yield "lead year starting in june" => [
            '2020-05-01T00:00:00+00:00',
            '2021-05-01T00:00:00+00:00',
            386751,
            100.000
        ];

        yield "year starting in june of a leap year (shouldn't matter)" => [
            '2020-06-01',
            '2021-06-01',
            386751,
            100.000
        ];

        yield 'first day of the year' => [
            '2020-01-01',
            '2020-01-02',
            166,
            0.04
        ];

        yield 'Only August' => [
            '2020-08-01',
            '2020-08-31',
            47638,
            12.32
        ];

        yield 'October to March (stepping into a new year)' => [
            '2019-10-01',
            '2020-03-31',
            82165,
            21.245
        ];

        yield 'October 2018 to March 2020 (more than 1 year)' => [
            '2018-10-01',
            '2020-03-31',
            468916,
            100.0 + 21.245
        ];

        yield 'End of year' => [
            '2018-12-31',
            '2019-01-01',
            232,
            0.06
        ];
    }

    public static function sumDataProvider(): Generator
    {
        $expectedDecember = 5385;
        yield 'December' => [
            '2018-12-01',
            '2019-01-01',
            $expectedDecember,
            1.392
        ];
        $expectedJanuary = 7019;
        yield 'January' => [
            '2019-01-01',
            '2019-02-01',
            $expectedJanuary,
            1.815
        ];

        yield 'December + January' => [
            '2018-12-01',
            '2019-02-01',
            $expectedDecember + $expectedJanuary,
            1.392 + 1.815
        ];

        yield 'full year - december' => [
            '2018-01-01',
            '2018-12-01',
            386751 - $expectedDecember,
            100.0 - 1.392
        ];

        // - - -

        $expectedJuneJuly = 116128;
        yield 'June and July' => [
            '2019-06-01',
            '2019-08-01',
            $expectedJuneJuly,
            30.03
        ];

        yield 'full year - (June and July)' => [
            '2019-08-01',
            '2020-06-01',
            386751 - $expectedJuneJuly,
            100.0 - 30.03
        ];

        yield ' 2 full year - (June and July)' => [
            '2018-08-01',
            '2020-06-01',
            386751 + 386751 - $expectedJuneJuly,
            200.0 - 30.03
        ];
    }

    /**
     * @dataProvider RadiationInPeriodDataProvider
     * @dataProvider sumDataProvider
     */
    public function testItReturnsTheRadiationForAPeriod(
        $start,
        $end,
        $expectedRadiation,
        $expectedPercentageOf7YAverage
    ) {
        $period = new Period(CarbonImmutable::parse($start), CarbonImmutable::parse($end));
        $this->assertEqualsWithDelta(
            $expectedRadiation,
            RadiationInPeriod::getAvgRadiation($period),
            0.01
        );
        $this->assertEqualsWithDelta(
            $expectedPercentageOf7YAverage,
            RadiationInPeriod::getRadiationPercentageOf7YAverage($period),
            0.01
        );
    }

    static function avgRadiationForDayProvider(): Generator
    {
        yield '1jan' => [1, 166, 1999];
        yield '20 aug' => [Carbon::parse('2011-08-20')->dayOfYear, 1610, 1999];
        yield '31 dec' => [365, 232, 1999];
        yield '31 dec on leap' => [366, 232, 2000];
    }

    /** @dataProvider avgRadiationForDayProvider */
    function testAvgRadiationForJulian(
        int  $julian,
        int  $expectedRad,
        int $year
    ) {
        $this->assertEquals(
            $expectedRad,
            RadiationInPeriod::getAvgRadiationForJulian($julian, $year)
        );
    }
}
