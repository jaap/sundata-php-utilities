<?php

namespace Sundata\Utilities\Test\Time;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sundata\Utilities\Time\DateRangeTz;
use Sundata\Utilities\Time\DateTz;

class DateRangeTzTest extends TestCase
{
    const DEFAULT_TZ = 'Europe/Amsterdam';

    function testBasic()
    {
        $dateRange = DateRangeTz::ofStrings(
            '2021-01-03',
            '2021-01-13',
            self::DEFAULT_TZ,
        );
        $this->assertEquals(10, $dateRange->nrDays());

        $dates = $dateRange->dates();
        $this->assertEquals(DateTz::of('2021-01-03', self::DEFAULT_TZ), $dates[0]);
        $this->assertEquals(DateTz::of('2021-01-12', self::DEFAULT_TZ), $dates[9]);
    }

    function testWithDates()
    {
        $dateRange = DateRangeTz::of(
            DateTz::of('2022-02-02', 'Europe/Amsterdam'),
            DateTz::of('2022-02-02', 'Europe/Amsterdam')
        );

        $this->assertEquals(0, $dateRange->nrDays());
        $this->assertEmpty($dateRange->dates());
    }

    function testWithBadDates()
    {
        $this->expectException(InvalidArgumentException::class);
        DateRangeTz::ofStrings(
            '2021-01-01',
            '2019-01-01',
            self::DEFAULT_TZ,
        );
    }

    function testWithBadTimezones()
    {
        $this->expectException(InvalidArgumentException::class);
        DateRangeTz::of(
            DateTz::of('2022-02-02', 'Europe/Amsterdam'),
            DateTz::of('2022-02-12', 'Europe/London')
        );
    }

    function testToString()
    {
        $dateRange = DateRangeTz::ofStrings(
            '2021-01-03',
            '2021-01-13',
            self::DEFAULT_TZ,
        );
        $this->assertEquals(
            'DateRangeTz: 2021-01-03 to 2021-01-13@Europe/Amsterdam',
            $dateRange->toString()
        );
    }

    static function periodDataProvider(): array
    {
        return [
            ['2022-01-01', 'Europe/Amsterdam', '2022-01-01T00:00:00+01:00', '2022-01-02T00:00:00+01:00'],
            ['2022-01-01', 'UTC', '2022-01-01T00:00:00+00:00', '2022-01-02T00:00:00+00:00'],
            ['2022-02-28', 'Europe/Amsterdam', '2022-02-28T00:00:00+01:00', '2022-03-01T00:00:00+01:00'],
        ];
    }

    /** @dataProvider periodDataProvider */
    function testPeriod($dateString, $tz, $expectedStart3339, $expectedEnd3339)
    {
        $period = DateTz::of($dateString, $tz)->asPeriod();
        $this->assertEquals($expectedStart3339, $period->getStart()->toRfc3339String());
        $this->assertEquals($expectedEnd3339, $period->getEnd()->toRfc3339String());
    }
}
