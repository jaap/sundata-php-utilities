<?php

namespace Sundata\Utilities\Test\Measurement;

use PHPUnit\Framework\TestCase;
use Sundata\Utilities\Measurement\Energy;
use Sundata\Utilities\Measurement\FormattedMeasurement;

class EnergyTest extends TestCase
{
    function testFormatReturnsTheRightClass()
    {
        $energy = Energy::fromKwh(1000);
        $this->assertInstanceOf(FormattedMeasurement::class, $energy->format());
    }

    public static function formatDataProvider(): array
    {
        return [
            [$kwh = 1, 'Wh'],
            [$kwh = 1000, 'kWh'],
            [$kwh = 10000, 'MWh'],
            [$kwh = 100000, 'MWh'],
            [$kwh = 1000000, 'MWh'],
            [$kwh = 10000000, 'GWh'],
            [$kwh = 100000000, 'GWh'],
            [$kwh = 1000000000, 'GWh'],
            [$kwh = 10000000000, 'TWh'],
            [$kwh = 10000000000000000000000, 'TWh'],
            [$kwh = 999999999999999999999999999999999999, 'TWh'],
        ];
    }

    /** @dataProvider formatDataProvider */
    public function testItFormatsToTheRightUnit($inputKwh, $expectedUnit)
    {
        $energy = Energy::fromKwh($inputKwh);
        $formattedMeasurement = $energy->format();
        $this->assertEquals($expectedUnit, $formattedMeasurement->unit);
    }

    public function testItDoesTheRightCalculation()
    {
        $kwh = 1000000000000;
        $energy = Energy::fromKwh(1000000000000);
        $this->assertEquals($kwh * 1000, $energy->asWh());
        $this->assertEquals($kwh, $energy->asKwh());
        $this->assertEquals($kwh / 1000, $energy->asMwh());
        $this->assertEquals($kwh / 1000 / 1000, $energy->asGwh());
        $this->assertEquals($kwh / 1000 / 1000 / 1000, $energy->asTwh());
    }

    public static function formatsToTheRightValueAndUnitDataProvider(): array
    {
        return [
            [54321, '54.32 MWh'],
            [0.9475, '947.5 Wh'],
            [1111.1, '1111.1 kWh'],
            [22.2222, '22.22 kWh'],
            [333.3333, '333.33 kWh'],
            [4444.4444, '4444.44 kWh'],
            [55555.55555, '55.56 MWh'],
        ];
    }

    /** @dataProvider formatsToTheRightValueAndUnitDataProvider */
    public function testItFormatsToTheRightValueAndUnit($inputKwh, $expectedString)
    {
        $energy = Energy::fromKwh($inputKwh);
        $this->assertEquals($expectedString, $energy->__toString());
    }

    public function testSettingPrecisionWorks()
    {
        $energy = Energy::fromKwh(21234567895);
        $this->assertEquals(21.23, $energy->format()->value);
        $this->assertEquals('TWh', $energy->format()->unit);

        $this->assertEquals(21.2, $energy->format(1)->value);
        $this->assertEquals('21.2 TWh', $energy->format(1)->__toString());
        $this->assertEquals(21.2346, $energy->format(4)->value);
        $this->assertEquals('21.2346 TWh', $energy->format(4)->__toString());
    }
}