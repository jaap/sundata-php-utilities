<?php

namespace Sundata\Utilities\Test\Time;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sundata\Utilities\Time\Date;

class DateTest extends TestCase
{
    function testInvalidDateFormatShouldFail()
    {
        $this->expectException(InvalidArgumentException::class);
        Date::of('22-02-22');
    }

    function testAddDays()
    {
        $date = Date::of('2021-01-01');
        $dateADayLater = $date->addDays(1);
        $this->assertEquals('2021-01-02', $dateADayLater->toDateString());
    }

    function testAddDaysLeaveOriginalUnChanged()
    {
        $date = Date::of('2021-01-01');
        $date->addDays(1);
        $this->assertEquals('2021-01-01', $date->toDateString());
    }

    function testAddDaysCanBeNegative()
    {
        $this->assertEquals(
            '2020-12-30',
            Date::of('2021-01-01')
                ->addDays(-2)
                ->toDateString()
        );
    }

    function testAddDaysCanBeZero()
    {
        $this->assertEquals(
            '2021-02-02',
            Date::of('2021-02-02')
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
        $dateA = Date::of($dateStringA);
        $dateB = Date::of($dateStringB);

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
            $this->assertEquals($date, Date::of($date)->toDateString());
        }
    }
}
