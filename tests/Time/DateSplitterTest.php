<?php


namespace Sundata\Utilities\Test\Time;


use Carbon\CarbonImmutable;
use Sundata\Utilities\Time\DateSplitter;
use Sundata\Utilities\Time\Period;
use PHPUnit\Framework\TestCase;

class DateSplitterTest extends TestCase
{

  public function hourSplitDataProvider()
  {
    return [

      ['2019-01-01T00:59:00Z', '2019-01-01T02:01:00Z', [
        ['2019-01-01T00:59:00Z', '2019-01-01T01:00:00Z'],
        ['2019-01-01T01:00:00Z', '2019-01-01T02:00:00Z'],
        ['2019-01-01T02:00:00Z', '2019-01-01T02:01:00Z'],
      ]],

      ['2019-01-01T00:00:00Z', '2019-01-01T04:20:00Z', [
        ['2019-01-01T00:00:00Z', '2019-01-01T01:00:00Z'],
        ['2019-01-01T01:00:00Z', '2019-01-01T02:00:00Z'],
        ['2019-01-01T02:00:00Z', '2019-01-01T03:00:00Z'],
        ['2019-01-01T03:00:00Z', '2019-01-01T04:00:00Z'],
        ['2019-01-01T04:00:00Z', '2019-01-01T04:20:00Z'],
      ]],

      ['2019-01-01T00:01:00Z', '2019-01-01T00:02:00Z',
        [['2019-01-01T00:01:00Z', '2019-01-01T00:02:00Z']]
      ],
      ['2019-01-01T00:01:00Z', '2019-01-01T00:01:00Z',
        [['2019-01-01T00:01:00Z', '2019-01-01T00:01:00Z']]
      ],
      ['2019-01-01T22:00:00Z', '2019-01-01T22:00:00Z',
        [['2019-01-01T22:00:00Z', '2019-01-01T22:00:00Z']]
      ],
      ['2019-01-01T20:00:00Z', '2019-01-01T22:00:00Z', [
        ['2019-01-01T20:00:00Z', '2019-01-01T21:00:00Z'],
        ['2019-01-01T21:00:00Z', '2019-01-01T22:00:00Z']
      ]],
    ];
  }

  /** @dataProvider hourSplitDataProvider */
  public function testHourSplit($start, $end, $expectedPeriodsArray)
  {
    $this->assertSplit($start, $end, $expectedPeriodsArray, 'splitInHours');
  }


  public function daySplitDataProvider()
  {
    return [
      ['2015-12-27T22:22:00+01:00', '2015-12-29T01:11:00+01:00', [
        ['2015-12-27T22:22:00+01:00', '2015-12-28T00:00:00+01:00'],
        ['2015-12-28T00:00:00+01:00', '2015-12-29T00:00:00+01:00'],
        ['2015-12-29T00:00:00+01:00', '2015-12-29T01:11:00+01:00'],
      ]],
    ];
  }

  /** @dataProvider daySplitDataProvider */
  public function testDaySplit($start, $end, $expectedPeriodsArray)
  {
    $this->assertSplit($start, $end, $expectedPeriodsArray, 'splitInDays');
  }

  public function weekSplitDataProvider()
  {
    return [
      ['2015-12-27T00:00:00Z', '2016-01-19T00:00:00Z', [
        ['2015-12-27T00:00:00Z', '2015-12-28T00:00:00Z'],
        ['2015-12-28T00:00:00Z', '2016-01-04T00:00:00Z'],
        ['2016-01-04T00:00:00Z', '2016-01-11T00:00:00Z'],
        ['2016-01-11T00:00:00Z', '2016-01-18T00:00:00Z'],
        ['2016-01-18T00:00:00Z', '2016-01-19T00:00:00Z'],
      ]]
    ];
  }

  /** @dataProvider weekSplitDataProvider */
  public function testWeekSplit($start, $end, $expectedPeriodsArray)
  {
    $this->assertSplit($start, $end, $expectedPeriodsArray, 'splitInWeeks');
  }


  public function monthSplitDataProvider()
  {
    return [
      ['2015-11-27T00:00:00Z', '2016-03-29T00:00:00Z', [
        ['2015-11-27T00:00:00Z', '2015-12-01T00:00:00Z'],
        ['2015-12-01T00:00:00Z', '2016-01-01T00:00:00Z'],
        ['2016-01-01T00:00:00Z', '2016-02-01T00:00:00Z'],
        ['2016-02-01T00:00:00Z', '2016-03-01T00:00:00Z'],
        ['2016-03-01T00:00:00Z', '2016-03-29T00:00:00Z'],
      ]],
    ];
  }

  /** @dataProvider monthSplitDataProvider */
  public function testMonthSplit($start, $end, $expectedPeriodsArray)
  {
    $this->assertSplit($start, $end, $expectedPeriodsArray, 'splitInMonths');
  }

  public function yearSplitDataProvider()
  {
    return [
      // Zulu time explicit
      ['2015-05-27T00:00:00Z', '2017-05-29T00:00:00Z', [
        ['2015-05-27T00:00:00Z', '2016-01-01T00:00:00Z'],
        ['2016-01-01T00:00:00Z', '2017-01-01T00:00:00Z'],
        ['2017-01-01T00:00:00Z', '2017-05-2900:00:00Z'],
      ]],
      // Local (default) timezone implicit
      ['2015-05-27', '2017-05-29', [
        ['2015-05-27T00:00:00+02:00', '2016-01-01T00:00:00+01:00'],
        ['2016-01-01T00:00:00+01:00', '2017-01-01T00:00:00+01:00'],
        ['2017-01-01T00:00:00+01:00', '2017-05-29T00:00:00+02:00'],
      ]],

      ['2015-05-27', '2015-05-29',
        [['2015-05-27T00:00:00+02:00', '2015-05-29T00:00:00+02:00']]],
      ['2015-05-27', '2015-12-31',
        [['2015-05-27T00:00:00+02:00', '2015-12-31T00:00:00+01:00']]],
      ['2015-05-27', '2016-01-01',
        [['2015-05-27T00:00:00+02:00', '2016-01-01T00:00:00+01:00']]],
    ];
  }

  /** @dataProvider yearSplitDataProvider */
  public function testYearSplit($start, $end, $expectedPeriodsArray)
  {
    $this->assertSplit($start, $end, $expectedPeriodsArray, 'splitInYears');
  }

  private function assertSplit($start, $end, $expectedPeriodsArray, string $methodName): void
  {
    $start = CarbonImmutable::parse($start, 'Europe/Amsterdam');
    $end = CarbonImmutable::parse($end, 'Europe/Amsterdam');

    $expectedPeriods = [];
    foreach ($expectedPeriodsArray as $period) {
      $expectedPeriods[] = new Period(
        CarbonImmutable::parse($period[0]),
        CarbonImmutable::parse($period[1])
      );
    }

    $period = DateSplitter::$methodName($start, $end);
    $this->assertEquals($expectedPeriods, $period);
  }
}