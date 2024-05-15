<?php

namespace Sundata\Utilities\Test\Time;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sundata\Utilities\Time\DateTz;

class DateTzTest extends TestCase
{
    const DEFAULT_TZ = 'Europe/Amsterdam';

    function testInvalidDateFormatShouldFail()
    {
        $this->expectException(InvalidArgumentException::class);
        DateTz::of('22-02-22', self::DEFAULT_TZ);
    }

    function testCantHaveWeirdTimezone()
    {
        $this->expectException(InvalidArgumentException::class);
        /** @noinspection SpellCheckingInspection */
        DateTz::of('2021-01-01', 'muhlocaltime');
    }

    function testConvertToCarbon()
    {
        $date = DateTz::of('2021-01-01', 'Europe/Amsterdam');
        $carbon = $date->asCarbonImmutable();
        $this->assertEquals('2021-01-01T00:00:00+01:00', $carbon->toRfc3339String());
    }

    function testAddDays()
    {
        $date = DateTz::of('2021-01-01', 'Europe/Amsterdam');
        $dateADayLater = $date->addDays(1);
        $this->assertEquals('2021-01-02', $dateADayLater->toDateString());
    }

    function testAddDaysLeaveOriginalUnChanged()
    {
        $date = DateTz::of('2021-01-01', 'UTC');
        $date->addDays(1);
        $this->assertEquals('2021-01-01', $date->toDateString());
    }

    function testAddDaysCanBeNegative()
    {
        $this->assertEquals(
            '2020-12-30',
            DateTz::of('2021-01-01', self::DEFAULT_TZ)
                ->addDays(-2)
                ->toDateString()
        );
    }

    function testAddDaysCanBeZero()
    {
        $this->assertEquals(
            '2021-02-02',
            DateTz::of('2021-02-02', self::DEFAULT_TZ)
                ->addDays(-0)
                ->toDateString()
        );
    }

    static function isBeforeAndIsAfterDataProvider(): array
    {
        return [
            ['2021-01-01', '2021-01-02', true, false],
            ['2021-01-02', '2021-01-01', false, false],
            ['2021-01-01', '2021-01-01', false, true],
        ];
    }

    /** @dataProvider isBeforeAndIsAfterDataProvider */
    function testIsBeforeAndIsAfterAndMinAndMax(
        $dateStringA,
        $dateStringB,
        bool $aBeforeB,
        bool $aEqualsB
    ) {
        $dateA = DateTz::of($dateStringA, self::DEFAULT_TZ);
        $dateB = DateTz::of($dateStringB, self::DEFAULT_TZ);

        if ($aEqualsB) {
            $this->assertEquals(false, $dateA->isBefore($dateB));
            $this->assertEquals(false, $dateA->isAfter($dateB));
            $this->assertEquals(false, $dateB->isBefore($dateA));
            $this->assertEquals(false, $dateB->isAfter($dateA));

        } else {
            $this->assertEquals($aBeforeB, $dateA->isBefore($dateB));
            $this->assertEquals(!$aBeforeB, $dateA->isAfter($dateB));
            $this->assertEquals(!$aBeforeB, $dateB->isBefore($dateA));
            $this->assertEquals($aBeforeB, $dateB->isAfter($dateA));
        }

        $this->assertEquals(
            $aBeforeB ? $dateA : $dateB,
            $dateA->min($dateB)
        );
        $this->assertEquals(
            $aBeforeB ? $dateB : $dateA,
            $dateA->max($dateB)
        );
    }

    public function testBeforeY2K()
    {
        foreach (['2000-01-01', '1999-12-31'] as $date) {
            $this->assertEquals($date, DateTz::of($date, self::DEFAULT_TZ)->toDateString());
        }
    }
}
