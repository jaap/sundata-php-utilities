<?php

namespace Sundata\Utilities\Test\Measurement;

use PHPUnit\Framework\TestCase;
use Sundata\Utilities\Measurement\Mass;
use Sundata\Utilities\Measurement\FormattedMeasurement;

class MassTest extends TestCase
{
    function testFormatReturnsTheRightClass()
    {
        $mass = Mass::fromKg(1000);
        $this->assertInstanceOf(FormattedMeasurement::class, $mass->format());
    }

    public static function formatDataProvider(): array
    {
        return [
            [$kg = 1, 'g'],
            [$kg = 1000, 'kg'],
            [$kg = 10000, 't'],
            [$kg = 100000, 't'],
            [$kg = 1000000, 't'],
            [$kg = 10000000, 't'],
            [$kg = 100000000, 'Mt'],
            [$kg = 1000000000, 'Mt'],
            [$kg = 10000000000, 'Mt'],
            [$kg = 10000000000000000000000, 'Gt'],
            [$kg = 999999999999999999999999999999999999, 'Gt'],
        ];
    }

    /** @dataProvider formatDataProvider */
    public function testItFormatsToTheRightUnit($inputKg, $expectedUnit)
    {
        $mass = Mass::fromKg($inputKg);
        $formattedMeasurement = $mass->format();
        $this->assertEquals($expectedUnit, $formattedMeasurement->unit);
    }

    public function testItDoesTheRightCalculation()
    {
        $kg = 100000000000;
        $mass = Mass::fromKg($kg);
        $this->assertEquals($kg * 1000, $mass->asGr());
        $this->assertEquals($kg, $mass->asKg());
        $this->assertEquals($kg / 1000, $mass->asT());
        $this->assertEquals($kg / 1000 / 1000 / 1000, $mass->asMt());
        $this->assertEquals($kg / 1000 / 1000 / 1000 / 1000, $mass->asGt());
    }

    public static function formatsToTheRightValueAndUnitDataProvider(): array
    {
        return [
            [5432, '5432 kg'],
            [0.9475, '947.5 g'],
            [1111.1, '1111.1 kg'],
            [55555.55555, '55.56 t'],
            [21234567, '21234.57 t'],
            [5555555555, '5.56 Mt'],
            [5555555555 * 1000, '5.56 Gt'],
        ];
    }

    /** @dataProvider formatsToTheRightValueAndUnitDataProvider */
    public function testItFormatsToTheRightValueAndUnit($inputKg, $expectedString)
    {
        $mass = Mass::fromKg($inputKg);
        $this->assertEquals($expectedString, $mass->__toString());
    }

    public function testSettingPrecisionWorks()
    {
        $mass = Mass::fromKg(21234567.1234);
        $this->assertEquals(21234.57, $mass->format()->value);
        $this->assertEquals('t', $mass->format()->unit);

        $this->assertEquals(21234.6, $mass->format(1)->value);
        $this->assertEquals('21234.6 t', $mass->format(1)->__toString());
        $this->assertEquals(21234.5671, $mass->format(4)->value);
        $this->assertEquals('21234.5671 t', $mass->format(4)->__toString());
    }
}