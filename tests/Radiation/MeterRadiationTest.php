<?php

namespace Sundata\Utilities\Test\Radiation;

use Carbon\CarbonImmutable;
use Generator;
use PHPUnit\Framework\TestCase;
use Sundata\Utilities\Radiation\MeterRadiation;
use Sundata\Utilities\Radiation\MeterFacts;

class MeterRadiationTest extends TestCase
{
    public static function MeterRadiationDataProvider(): Generator
    {
        yield 'South facing 80 degrees should return 107 W/m2' => [
            new MeterFacts(
                52.1009166,
                5.36237110,
                80,
                180
            ),
            new CarbonImmutable('2021-11-01 11:00:00'),
            111,
            107.6038497321954];

        yield 'East facing 80 degrees should return 18 W/m2' => [
            new MeterFacts(
                52.1009166,
                5.36237110,
                80,
                90
            ),
            new CarbonImmutable('2021-11-01 11:00:00'),
            111,
            18.049368565163366];

        yield 'West facing 40 degrees should return 26 W/m2' => [
            new MeterFacts(
                52.1009166,
                5.36237110,
                40,
                270
            ),
            new CarbonImmutable('2021-11-01 11:00:00'),
            111,
            26.35906520119014];
    }

    /** @dataProvider MeterRadiationDataProvider */
    public function testItReturnsMeterIrradiance(
        MeterFacts      $request,
        CarbonImmutable $dateTime,
        float           $globalIrradiance,
        float           $expectedIrradiance
    ) {
        $rad = MeterRadiation::calculateIrradianceForDateTime($request, $dateTime, $globalIrradiance);
        $this->assertEqualsWithDelta($expectedIrradiance, $rad, 1E-9);
    }
}
